<?php

namespace Suphle\Routing\Analysis;

use Suphle\Contracts\Config\{Router as RouterConfig};
use Suphle\Hydration\{Container, Structures\ObjectDetails};
use Suphle\Contracts\{Database\ModelSchemaDetector};
use Suphle\Exception\Explosives\{Unauthenticated, UnauthorizedServiceAccess};
use Suphle\Services\Structures\ModelfulPayload;
use PhpParser\{Node, Expr};
use PhpParser\Expr\{MethodCall, Variable, StaticCall, Array_, New_, Ternary, Match_};
use PhpParser\Node\{Identifier, Name};
use ReflectionMethod, ReflectionNamedType, ReflectionException, ReflectionClass, ReflectionProperty;

/*
1) account for multiple return types
2) inside the renderer, pick out both arg and return val. send arg to orm delegate to distill model type if it matches mfp. then use this hint on orm to determine return val (typically said model ref or a collection of it)

*/

class RendererContentShape extends RouteAnalysisService
{
    use AnalyzerUtils, DocBlockParser, AstHelper;

    protected ReflectionMethod $actionMethod;

    public function __construct(
        RouterConfig $config,
        Container $container,
        ObjectDetails $objectDetails,
        protected readonly ModelSchemaDetector $modelDetector,
    ) {
        parent::__construct($config, $container, $objectDetails);
        $this->setNodeFinder();
    }

    /**
     * Parses the method body, finds the return expression, and dispatches
     * through resolveExpression(). Since all coordinator actions return a
     * framework renderer (Json, etc.), the top-level node will always be
     * a New_ — resolveRendererInstantiation() unwraps it to reach the
     * actual content passed in, which is the shape we document.
     */
    public function getResponseShape(ReflectionMethod $method): array
    {
        $this->actionMethod = $method;

        $stmts  = $this->getMethodAst($method);
        $return = $this->findAllReturnExpressions($stmts);

        if (empty($returns)) {
            return ['type' => 'object'];
        }

        $shapes = [];
        foreach ($returns as $returnNode) {
            // $returnNode is an instance of PhpParser\Node\Stmt\Return_
            // We pass the actual underlying expression ($returnNode->expr) to resolveExpression
            $resolved = $this->resolveExpression(
                $returnNode->expr, 
                $method->getDeclaringClass()->getName()
            );
            if ($resolved) {
                $shapes[] = $resolved;
            }
        }

        $unique = array_unique($shapes, SORT_REGULAR);

        if (empty($unique)) {
            return ['type' => 'object'];
        }

        return count($unique) === 1
            ? $unique[0]
            : ['oneOf' => array_values($unique)];
    }

    protected function resolveExpression(?Expr $expr, string $ctx): ?array
    {
        if (!$expr) return null;

        return match (true) {
            $expr instanceof New_       => $this->resolveRendererInstantiation($expr, $ctx),
            $expr instanceof Array_     => $this->resolveArray($expr, $ctx),
            $expr instanceof Variable   => $this->resolveVariable($expr, $ctx),
            $expr instanceof MethodCall => $this->resolveMethodCall($expr, $ctx),
            $expr instanceof StaticCall => $this->resolveStaticCall($expr, $ctx),
            $expr instanceof Ternary    => $this->resolveTernary($expr, $ctx),
            $expr instanceof Match_     => $this->resolveMatch($expr, $ctx),
            default                     => ['type' => 'object'],
        };
    }

    /**
     * Handles: return new Json([...]) / return new Json($data)
     *
     * All coordinator actions return a framework renderer. The shape we
     * care about is the content passed as its first argument, not the
     * renderer itself. We unwrap and re-dispatch that argument.
     *
     * If the renderer has no argument (e.g. new Json()), we emit a bare
     * object since there is no content to infer.
     */
    protected function resolveRendererInstantiation(New_ $expr, string $ctx): ?array
    {
        $arg = $expr->args[0]->value ?? null;

        return $arg ? $this->resolveExpression($arg, $ctx) : ['type' => 'object'];
    }

    /**
     * Handles: ['key' => $value, ...]
     *
     * Each value is recursively resolved, so arrays containing method
     * call results or variable references are handled correctly.
     * Keys without a static string literal are skipped.
     */
    protected function resolveArray(Array_ $expr, string $ctx): array
    {
        $properties = [];

        foreach ($expr->items as $item) {
            if (!$item || !$item->key) continue;

            $key              = $item->key->value;
            $properties[$key] = $this->resolveExpression($item->value, $ctx);
        }

        return [
            'type'       => 'object',
            'properties' => $properties,
        ];
    }

    /**
     * Handles: $data (as an argument to a renderer)
     *
     * Traces $data back to its last assignment within the method body,
     * then re-dispatches through resolveExpression(). For example:
     *   $data = $employmentBuilder->getBuilder()->get();
     *   return new Json($data);
     * resolves identically to passing the chain directly into Json.
     */
    protected function resolveVariable(Variable $expr, string $ctx): ?array
    {
        if (!is_string($expr->name)) return null;

        $assignment = $this->findVariableAssignment($expr->name, $this->actionMethod);

        if (!$assignment) return null;

        return $this->resolveExpression($assignment, $ctx);
    }

    /**
     * Handles: $employmentBuilder->getBuilder()->with(...)->get()
     *
     * Unwinds the MethodCall chain right-to-left (outermost call first),
     * reverses to canonical order, then resolves the root variable's class.
     *
     * Example: $employmentBuilder->getBuilder()->with('user')->get()
     *   PhpParser tree (outermost → innermost):
     *     MethodCall(get)
     *       MethodCall(with)
     *         MethodCall(getBuilder)
     *           Variable(employmentBuilder)
     *   Collected before reverse: ['get', 'with', 'getBuilder']
     *   After reverse:            ['getBuilder', 'with', 'get']
     *   $current after loop:       Variable('employmentBuilder')
     *
     * Root class resolution for $current — see resolveVariableClass().
     *
     * NOTE FOR FUTURE CONTRIBUTORS — carrying with() arguments:
     * Currently only method names are collected; arguments are discarded.
     * This is sufficient for cardinality inference (single vs collection).
     * If you want relation narrowing (schema shows only eager-loaded relations
     * rather than all defined relations on the model), change this method to
     * produce a richer IR that carries the string arguments of with() calls:
     *   ['getBuilder', ['with', ['employment', 'user']], 'get']
     * and update ESD's resolveTerminalModel() to consume the extra data.
     * See the corresponding note in EloquentSchemaDetector::resolveTerminalModel().
     */
    protected function resolveMethodCall(MethodCall $call, string $ctx): ?array
    {
        $chain   = [];
        $current = $call;

        while ($current instanceof MethodCall) {
            if ($current->name instanceof Identifier) {
                $chain[] = $current->name->toString();
            }
            $current = $current->var;
        }

        $chain        = array_reverse($chain);
        $contextClass = $ctx;

        if ($current instanceof Variable && is_string($current->name)) {
            $contextClass = $this->resolveVariableClass($current->name, $ctx);
        }

        // ORM territory (ModelfulPayload / BaseModel) is ESD's job exclusively.
        // Anything else — plain services, ModellessPayload-derived DTOs, etc. —
        // is not an ORM concern and must never be routed through ESD. Instead
        // we ask PHP itself: does the terminal method on $contextClass declare
        // a return type? If so, that IS the shape, no ORM tracing involved.
        if ($this->modelDetector->isOrmRelevant($contextClass)) {
            return $this->modelDetector->resolveCallChain($contextClass, $chain);
        }

        return $this->resolveFromNativeReturnType($contextClass, $chain, $ctx);
    }

    /**
     * Resolves shape from the terminal method's own declared PHP return type.
     *
     * Covers the case raised by ModellessPayload-backed services:
     *   $this->transactionService->updateModels($payloadReader->getDomainObject())
     *
     * $payloadReader->getDomainObject() is an INPUT to updateModels, not the
     * shape we're documenting — its own type is irrelevant here and is never
     * inspected. We only care about updateModels()'s declared return type.
     *
     * If that return type is itself a class (a DTO, a model, a collection
     * wrapper, etc.), we recurse: a concrete class with a getBuilder()-style
     * ORM shape goes back through resolveChain via isOrmRelevant(); a plain
     * DTO/value class falls through to schemaFromReflectedClass(), which
     * reads its public properties. Builtins (string, int, array, etc.) map
     * directly. No declared return type at all yields a bare object, which
     * is the same honest fallback the rest of the engine uses.
     */
    protected function resolveFromNativeReturnType(string $contextClass, array $chain, string $ctx): array
    {
        $terminalMethod = end($chain);

        if (!$terminalMethod || !method_exists($contextClass, $terminalMethod)) {
            return ['type' => 'object'];
        }

        try {
            $reflected = new ReflectionMethod($contextClass, $terminalMethod);
        } catch (ReflectionException) {
            return ['type' => 'object'];
        }

        $type = $reflected->getReturnType();

        if (!$type instanceof ReflectionNamedType) {
            return ['type' => 'object'];
        }

        if ($type->isBuiltin()) {
            return $this->mapNativeType($type->getName());
        }

        $returnedClass = $type->getName();

        // The declared return type might itself be ORM-relevant (e.g. a
        // service method that returns Collection of models indirectly) —
        // give ESD a chance with an empty chain before falling to a DTO read.
        if ($this->modelDetector->isOrmRelevant($returnedClass)) {
            return $this->modelDetector->resolveCallChain($returnedClass, []);
        }

        return $this->schemaFromReflectedClass($returnedClass);
    }

    /**
     * Builds a shallow object schema from a plain class's public properties
     * (constructor-promoted or declared). Used for DTOs / domain objects
     * like GenericPaidDSL that aren't models and have no ORM meaning —
     * we simply expose their public surface as-is.
     */
    protected function schemaFromReflectedClass(string $className): array
    {
        if (!class_exists($className)) {
            return ['type' => 'object'];
        }

        try {
            $ref = new ReflectionClass($className);
        } catch (ReflectionException) {
            return ['type' => 'object'];
        }

        $properties = [];

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $type = $prop->getType();

            $properties[$prop->getName()] = $type instanceof ReflectionNamedType && $type->isBuiltin()
                ? $this->mapNativeType($type->getName())
                : ['type' => 'object'];
        }

        // Constructor-promoted properties aren't always picked up by
        // getProperties() depending on PHP version quirks with promotion +
        // visibility; this is a known shallow spot — extend here if your
        // DTOs rely heavily on promoted constructor properties going undetected.

        return [
            'type'       => 'object',
            'properties' => $properties,
        ];
    }

    protected function mapNativeType(string $type): array
    {
        return match ($type) {
            'int'             => ['type' => 'integer'],
            'float'           => ['type' => 'number'],
            'bool'            => ['type' => 'boolean'],
            'string'          => ['type' => 'string'],
            'array', 'iterable' => ['type' => 'array', 'items' => ['type' => 'object']],
            default           => ['type' => 'object'],
        };
    }

    /**
     * Resolves the FQCN of a variable by name across injection sites.
     *
     * Priority order reflects how dependencies actually arrive in Suphle:
     *   1. Action method parameter — builder types like BaseEmploymentBuilder
     *      are injected directly into the action signature, e.g.:
     *        public function getEmploymentDetails(BaseEmploymentBuilder $employmentBuilder)
     *   2. Constructor parameter — for service-level deps injected at coordinator level.
     *   3. Fall back to $ctx (coordinator class) — ESD returns ['type'=>'object'].
     *
     * Inline instantiation (new ClassName) is not listed because Suphle
     * dependencies are always injected, never manually constructed.
     */
    protected function resolveVariableClass(string $varName, string $ctx): string
    {
        // Priority 1: action method parameter
        foreach ($this->actionMethod->getParameters() as $param) {
            if ($param->getName() === $varName) {
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    return $type->getName();
                }
            }
        }

        // Priority 2: constructor parameter
        $paramTypes = $this->getConstructorParamTypes(
            $this->actionMethod->getDeclaringClass()->getName()
        );

        return $paramTypes[$varName] ?? $ctx;
    }

    /**
     * Handles bare static calls: Employment::all()
     *
     * For chained static calls like Employment::query()->get(), PhpParser
     * represents this as MethodCall(get, var: StaticCall(query, Employment)).
     * resolveMethodCall() unwinds the instance chain first and hits the
     * StaticCall as $current — that case never reaches here.
     *
     * This method only handles a StaticCall that is itself the outermost node:
     *   Employment::all()
     *   → contextClass = Employment, chain = ['all']
     */
    protected function resolveStaticCall(StaticCall $call, string $ctx): ?array
    {
        if (!$call->class instanceof Name) {
            return ['type' => 'object'];
        }

        $contextClass = $call->class->toString();
        $chain        = [];

        if ($call->name instanceof Identifier) {
            $chain[] = $call->name->toString();
        }

        return $this->modelDetector->resolveCallChain($contextClass, $chain);
    }

    /**
     * Handles: $condition ? $employmentBuilder->getBuilder()->get() : $employmentBuilder->getBuilder()->first()
     *
     * Both branches are resolved independently. Identical schemas are collapsed
     * to avoid a needless oneOf. Differing schemas emit oneOf so the consumer
     * sees the full set of possible shapes.
     *
     * PHP short ternary ($a ?: $b): PhpParser sets $expr->if to null.
     * We fall back to $expr->cond as the "if" branch — semantically correct
     * since a truthy condition is itself the returned value.
     *
     * Limitation: only a ternary in the return statement is handled here.
     * Multiple return statements inside if/else branches are not yet collected
     * — findReturnExpression() returns the first one only. To support that,
     * replace findReturnExpression() with a collector that gathers all Return_
     * nodes in the method body and merges their resolved schemas into a oneOf.
     */
    protected function resolveTernary(Ternary $expr, string $ctx): array
    {
        $ifBranch   = $this->resolveExpression($expr->if ?? $expr->cond, $ctx);
        $elseBranch = $this->resolveExpression($expr->else, $ctx);

        if ($ifBranch === $elseBranch) {
            return $ifBranch ?? ['type' => 'object'];
        }

        return [
            'oneOf' => array_values(array_filter([$ifBranch, $elseBranch])),
        ];
    }

    /**
     * Handles: match ($canary) { 'beta' => $service->customHandlePrevious($input), default => $service->bar() }
     *
     * Same principle as resolveTernary, generalized to N arms. Each arm's
     * body is resolved independently (the match conditions themselves are
     * never inspected — only the returned shapes matter). Identical shapes
     * across all arms collapse to one; differing shapes emit oneOf.
     *
     * A match with no default arm is still handled the same way: we only
     * look at whatever arms are present in the AST, we don't try to prove
     * exhaustiveness.
     */
    protected function resolveMatch(Match_ $expr, string $ctx): array
    {
        $shapes = [];

        foreach ($expr->arms as $arm) {
            $shape = $this->resolveExpression($arm->body, $ctx);

            if ($shape) $shapes[] = $shape;
        }

        if (empty($shapes)) return ['type' => 'object'];

        $unique = array_unique($shapes, SORT_REGULAR);

        return count($unique) === 1
            ? $unique[0]
            : ['oneOf' => array_values($unique)];
    }

    public function getAuthenticationErrorMessage(): string
    {
        return $this->getDiffuserMessage(Unauthenticated::class);
    }

    public function getAuthorizationErrorMessage(): string
    {
        return $this->getDiffuserMessage(UnauthorizedServiceAccess::class);
    }

    protected function getDiffuserMessage(string $exceptionType): string
    {
        $diffuser = $this->config->getHandlers()[$exceptionType];

        return $diffuser::RAW_RESPONSE[$diffuser::ERRORS_PRESENCE];
    }
}