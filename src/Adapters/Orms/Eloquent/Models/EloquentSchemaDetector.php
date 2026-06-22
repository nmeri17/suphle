<?php

namespace Suphle\Adapters\Orms\Eloquent\Models;

use Suphle\Services\Structures\ModelfulPayload;
use Suphle\Hydration\Structures\ObjectDetails;
use Suphle\Routing\Analysis\AstHelper;
use Suphle\Contracts\{Database\ModelSchemaDetector, Config\Database as DatabaseConfig};
use Suphle\File\FileSystemReader;
use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionException, Throwable;

class EloquentSchemaDetector implements ModelSchemaDetector
{
    use AstHelper;

    protected array $registry         = [];
    protected array $discoveredModels = [];

    public function __construct(
        protected readonly ObjectDetails $objectDetails,
        protected readonly DatabaseConfig $databaseConfig,
        protected readonly FileSystemReader $fileReader,
    ) {
        $this->setNodeFinder();
    }

    /**
     * True only for classes ESD knows how to interpret. Everything else
     * (plain services, ModellessPayload subclasses, value objects) is
     * explicitly out of ESD's domain and must be resolved here in RCS
     * via ordinary PHP return-type reflection instead.
     */
    public function isOrmRelevant(string $contextClass): bool
    {
        return is_subclass_of($contextClass, ModelfulPayload::class)
            || is_subclass_of($contextClass, BaseModel::class);
    }
    /**
     * Single entry point for PSA → ESD delegation.
     *
     * PSA passes:
     * $contextClass — the FQCN it resolved as the root of the call chain
     * (from New_ instantiation or constructor param type hint)
     * $chain        — ordered method names collected from the AST, e.g.
     * ['getBuilder', 'with', 'where', 'get']
     *
     * ESD owns all ORM interpretation from this point forward.
     */
    public function resolveCallChain(string $contextClass, array $chain): array
    {
        $registryKey = $contextClass . '::' . implode('>', $chain);

        return $this->registry[$registryKey] ??= $this->resolveChain($contextClass, $chain);
    }
    /**
     * Routes $contextClass to the right resolution path.
     *
     * ModelfulPayload subclasses go through builder inference first to
     * extract the underlying model. Direct BaseModel subclasses (e.g.
     * Employment::query()->get() in a coordinator) skip straight to
     * terminal resolution since the model is already known.
     */
    protected function resolveChain(string $contextClass, array $chain): array
    {
        if ($this->isModelfulPayload($contextClass)) {
            return $this->resolveModelfulChain($contextClass, $chain);
        }

        if (is_subclass_of($contextClass, BaseModel::class)) {
            return $this->resolveTerminalModel($contextClass, $chain);
        }

        return ['type' => 'object'];
    }

    // -------------------------------------------------------------------------
    // MODELFUL PAYLOAD HANDLING
    // -------------------------------------------------------------------------

    /**
     * Infers the model from the payload's getBaseCriteria() body, then
     * delegates to terminal resolution with that model.
     *
     * Example: BaseEmploymentBuilder → Employment (via AST of getBaseCriteria)
     * → resolveTerminalModel('Employment', ['getBuilder', 'first'])
     */
    protected function resolveModelfulChain(string $payloadClass, array $chain): array
    {
        $model = $this->inferBuilderModel($payloadClass);

        if (!$model || !class_exists($model)) {
            return ['type' => 'object'];
        }

        return $this->resolveTerminalModel($model, $chain);
    }

    // -------------------------------------------------------------------------
    // TERMINAL RESOLUTION
    // -------------------------------------------------------------------------

    /**
     * Determines response cardinality from the terminal method in the chain.
     *
     * The terminal is always end($chain) because PSA's chain collector
     * reverses traversal order, making the outermost (last-called) method
     * the final element. For example:
     * $builder->getBuilder()->with('user')->where(...)->get()
     * produces ['getBuilder', 'with', 'where', 'get'] — terminal is 'get'.
     *
     * NOTE FOR FUTURE CONTRIBUTORS — relation narrowing via with():
     * Currently, modelToSchema() always includes every relation defined
     * on the model, regardless of whether with() was called in the chain.
     * This is intentional: for docs gen, "what can this response contain"
     * is more useful than "what is hydrated in this specific call."
     *
     * If you want to narrow the schema to only eager-loaded relations:
     * 1. Change PSA's resolveMethodCall() to carry with() arguments in
     * the chain IR, e.g. ['getBuilder', ['with', ['employment']], 'get']
     * 2. Extract those relation names here before calling modelToSchema().
     * 3. Pass them as a filter into modelToSchema() so only matching
     * relation methods are included in $properties.
     * Tradeoff: added IR complexity for marginal documentation precision gain.
     */
    protected function resolveTerminalModel(string $model, array $chain): array
    {
        $terminal = end($chain);
        $ref      = $this->registerModel($model);

        return match ($terminal) {
            'first', 'find', 'findOrFail', 'firstOrFail' =>
                $ref,

            'get', 'paginate', 'cursor', 'all' => [
                'type'  => 'array',
                'items' => $ref,
            ],

            // Unknown terminal — likely a scalar aggregate (count, sum, pluck, value, etc.)
            // Returning a bare object is safer than a wrong model ref.
            // To support scalar terminals, map them explicitly above this default.
            default => ['type' => 'object'],
        };
    }

    // -------------------------------------------------------------------------
    // MODEL INFERENCE
    // -------------------------------------------------------------------------

    /**
     * Resolves the Eloquent model a ModelfulPayload operates on by
     * statically parsing its getBaseCriteria() method body.
     *
     * All ModelfulPayload subclasses define getBaseCriteria() as an
     * abstract contract, so reflection will always find it. We parse
     * its AST rather than executing it to remain static-analysis-safe.
     *
     * Resolution order:
     * 1. extractModelFromAst()     — finds Employment::query() or ->model(Employment::class)
     * 2. guessModelFromClassName() — strips builder suffix, looks up in configured namespace. is a last resort. It works when the payload
     * class name encodes the model (e.g. BaseEmploymentBuilder → Employment).
     * It will produce a wrong FQCN for payloads whose name doesn't follow
     * that convention — a warning log here would aid debugging.\
     */
    protected function inferBuilderModel(string $payloadClass): ?string
    {
        try {
            $ref    = $this->objectDetails->getReflectedClass($payloadClass);
            $method = $ref->getMethod('getBaseCriteria');
            $stmts  = $this->getMethodAst($method);

            return $this->extractModelFromAst($stmts, $payloadClass, BaseModel::class)
            ?? $this->guessModelFromClassName($payloadClass);

        } catch (ReflectionException) {
            return $this->guessModelFromClassName($payloadClass);
        }
    }

    protected function guessModelFromClassName(string $payloadClass): string
    {
        $parts = explode('\\', $payloadClass);
        $baseName = end($parts);
        
        // Clean common prefixes/suffixes
        $cleaned = preg_replace('/^(Base|Abstract|)(.+?)(Builder|Payload|Repository|Criteria)?$/', '$2', $baseName);

        $modelNamespace = $this->databaseConfig->componentInstallNamespace();
        $candidate = $modelNamespace . '\\' . $cleaned;

        if (class_exists($candidate)) {
            return $candidate;
        }

        // Fallback: scan models directory for closest match
        $modelsPath = $this->databaseConfig->componentInstallPath();

        if (is_dir($modelsPath)) {
            $bestMatch = $this->findClosestModelFile($modelsPath, $cleaned);
            if ($bestMatch) {
                return $modelNamespace . '\\' . $bestMatch;
            }
        }

        return $candidate; // last resort
    }

    protected function findClosestModelFile(string $modelsDir, string $targetName): ?string
    {
        $best = null;
        $bestScore = 0;

        $this->fileReader->iterateDirectory(
            $modelsDir,
            fn() => null, // skip subdirs for now
            function ($fullPath, $fileName) use ($targetName, &$best, &$bestScore) {
                $className = pathinfo($fileName, PATHINFO_FILENAME);
                
                // Simple similarity (can improve with levenshtein if needed)
                $score = similar_text(strtolower($className), strtolower($targetName), $percent);
                
                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $best = $className;
                }
            }
        );

        return $bestScore > 70 ? $best : null; // threshold
    }
    /**
     * Builds an OpenAPI-style object schema for a single model class.
     *
     * Column types come from the live schema builder — this requires a DB
     * connection at docs-gen time, which is acceptable since docs gen is a
     * dev-time operation. Consider caching getColumnListing results if you
     * run this over large schemas repeatedly.
     *
     * Relations are discovered via return-type reflection on public methods.
     * Every defined relation is included regardless of eager loading — see
     * the note in resolveTerminalModel() if you want to narrow this.
     */
    public function modelToSchema(string $className): array
    {
        if (!class_exists($className)) {
            return ['type' => 'object'];
        }

        $instance = $this->objectDetails->noConstructor($className);
        $builder  = $instance->getConnection()->getSchemaBuilder();
        $table    = $instance->getTable();
        $hidden   = $instance->getHidden();

        $properties = [];

        foreach ($builder->getColumnListing($table) as $col) {
            if (in_array($col, $hidden)) continue;

            $properties[$col] = [
                'type' => $this->mapDbType($builder->getColumnType($table, $col)),
            ];
        }

        foreach ($this->objectDetails->getPublicMethods($className) as $method) {
            $returnType = $this->objectDetails->methodReturnType($className, $method);

            if (!$returnType || !is_subclass_of($returnType, Relation::class)) continue;

            try {
                // Instantiate the relation to discover the related model FQCN,
                // then register it so it appears in getGeneratedSchemas() output
                $related = get_class($instance->$method()->getRelated());
                $isMany  = str_contains($returnType, 'Many');
                $ref     = $this->registerModel($related);

                $properties[$method] = $isMany
                    ? ['type' => 'array', 'items' => $ref]
                    : $ref;

            } catch (Throwable) {
                // Relation instantiation can fail if the related table doesn't
                // exist yet or the model has required constructor dependencies.
                // Skip gracefully — the relation simply won't appear in the schema.
                continue;
            }
        }

        return [
            'type'       => 'object',
            'properties' => $properties,
        ];
    }

    // -------------------------------------------------------------------------
    // MODEL REGISTRY / HARVESTING
    // -------------------------------------------------------------------------

    /**
     * Records a model as discovered and returns its $ref pointer.
     *
     * Called from resolveTerminalModel() when a chain resolves to a model,
     * and from modelToSchema() when a relation's related class is encountered.
     * This ensures the full transitive closure of referenced models is captured
     * and available via getGeneratedSchemas() at the end of a docs-gen pass.
     */
    public function registerModel(string $className): array
    {
        $className = ltrim($className, '\\');

        if (!in_array($className, $this->discoveredModels)) {
            $this->discoveredModels[] = $className;
        }

        return [
            '$ref' => '#/components/schemas/' . str_replace('\\', '_', $className),
        ];
    }

    /**
     * Returns the full OpenAPI component schema map for every model
     * encountered during a docs-gen pass.
     *
     * Call this after all coordinator action methods have been analyzed
     * to collect the complete set of schemas for the components section.
     */
    public function getGeneratedSchemas(): array
    {
        $out = [];

        foreach ($this->discoveredModels as $model) {
            $out[str_replace('\\', '_', $model)] = $this->modelToSchema($model);
        }

        return $out;
    }

    protected function mapDbType(string $type): string
    {
        return match ($type) {
            'integer', 'bigint', 'smallint' => 'integer',
            'boolean'                        => 'boolean',
            'decimal', 'float', 'double'    => 'number',
            default                          => 'string',
        };
    }

    protected function isModelfulPayload(string $class): bool
    {
        return is_subclass_of($class, ModelfulPayload::class);
    }
}