<?php

namespace Suphle\Routing\Analysis;

use PhpParser\{ParserFactory, NodeFinder, Node, Expr};
use PhpParser\Expr\{MethodCall, Variable, Assign, ClassConstFetch, StaticCall, PropertyFetch};
use PhpParser\Node\{Identifier, Name, Stmt\Return_, Stmt\Class_, Stmt\ClassMethod};
use PhpParser\ParserAbstract;
use ReflectionMethod, ReflectionClass, ReflectionNamedType, ReflectionException;

trait AstHelper
{
    protected NodeFinder $nodeFinder;
    protected ParserAbstract $parser;

    protected function setNodeFinder(): void
    {
        $this->nodeFinder = new NodeFinder();
        $this->parser = (new ParserFactory())->createForHostVersion();
    }

    /**
     * Returns only the statements inside the target method body,
     * not the entire file. This prevents findReturnExpression and
     * findVariableAssignment from accidentally matching nodes in
     * sibling methods defined earlier in the same file.
     */
    protected function getMethodAst(ReflectionMethod $method): array
    {
        $file = $method->getFileName();

        if (!$file || !file_exists($file)) return [];

        $code = file_get_contents($file);
        $fileAst = $this->parser->parse($code);

        if (!$fileAst) return [];

        $classNode = $this->nodeFinder->findFirst(
            $fileAst,
            fn(Node $n) =>
                $n instanceof Class_ &&
                $n->name?->toString() === $method->getDeclaringClass()->getShortName()
        );

        if (!$classNode) return [];

        $methodNode = $this->nodeFinder->findFirst(
            [$classNode],
            fn(Node $n) =>
                $n instanceof ClassMethod &&
                $n->name->toString() === $method->getName()
        );

        return $methodNode?->stmts ?? [];
    }

    protected function findAllReturnExpressions(array $ast): array
    {
        return $this->nodeFinder->findInstanceOf($ast, Return_::class);
    }

    protected function findVariableAssignment(string $varName, ReflectionMethod $method): ?Expr
    {
        $stmts = $this->getMethodAst($method);
        $assignments = $this->nodeFinder->findInstanceOf($stmts, Assign::class);

        // Reverse to get the closest (last) assignment before the return
        foreach (array_reverse($assignments) as $assign) {
            if ($assign->var instanceof Variable && $assign->var->name === $varName) {
                return $assign->expr;
            }
        }

        return null;
    }

    /**
     * Resolves the declared type of a constructor parameter by name.
     *
     * This is the correct way to identify injected ModelfulPayload
     * dependencies in Suphle, where constructor injection is the
     * dominant and preferred pattern over inline instantiation.
     *
     * Given a coordinator like:
     * __construct(private BaseEmploymentBuilder $employmentBuilder)
     *
     * getConstructorParamTypes(CoordinatorClass::class) returns:
     * ['employmentBuilder' => 'Acme\Builders\BaseEmploymentBuilder']
     *
     * PSA then uses this to resolve $employmentBuilder → its FQCN before
     * passing contextClass to ESD.
     */
    protected function getConstructorParamTypes(string $className): array
    {
        try {
            $ref = new ReflectionClass($className);
            $constructor = $ref->getConstructor();

            if (!$constructor) return [];

            $map = [];

            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $map[$param->getName()] = $type->getName();
                }
            }

            return $map;

        } catch (ReflectionException) {
            return [];
        }
    }

    /**
     * Extracts a model FQCN from the AST of a getBaseCriteria() body.
     *
     * Focused on Suphle's actual patterns:
     *
     * 1. Static calls: return Employment::query() / Employment::where(...)
     * 2. Property access on injected model: return $this->blankEmployment->where(...)
     */
    protected function extractModelFromAst(array $methodStmts, string $payloadClass, string $baseModel): ?string
    {
        // Pattern 1: Static calls (Employment::where(...), Employment::query(), etc.)
        $staticCalls = $this->nodeFinder->findInstanceOf($methodStmts, StaticCall::class);

        foreach ($staticCalls as $call) {
            if ($call->class instanceof Name) {
                $candidate = $call->class->toString();
                if (!in_array($candidate, ['self', 'static', 'parent', 'DB', 'Schema', $baseModel])) {
                    return $candidate;
                }
            }
        }

        // Pattern 2: $this->propertyName->where() / ->get() / ->first() etc.
        $methodCalls = $this->nodeFinder->findInstanceOf($methodStmts, MethodCall::class);

        foreach ($methodCalls as $call) {
            if ($call->var instanceof PropertyFetch &&
                $call->var->var instanceof Variable &&
                $call->var->var->name === 'this') {

                $propertyName = $call->var->name->name ?? null;

                if ($propertyName) {
                    $model = $this->getModelTypeFromProperty($payloadClass, $propertyName, $baseModel);
                    if ($model) {
                        return $model;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Resolves the model type from a constructor-injected property
     * (including promoted properties).
     */
    protected function getModelTypeFromProperty(string $payloadClass, string $propertyName, string $baseModel): ?string
    {
        try {
            $refClass = $this->objectDetails->getReflectedClass($payloadClass);

            // Check constructor parameters (best for promoted properties)
            $constructor = $refClass->getConstructor();
            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    if ($param->getName() === $propertyName) {
                        $type = $param->getType();
                        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                            $candidate = $type->getName();
                            if (is_subclass_of($candidate, $baseModel)) {
                                return $candidate;
                            }
                        }
                    }
                }
            }

            // Fallback: check regular property
            if ($refClass->hasProperty($propertyName)) {
                $property = $refClass->getProperty($propertyName);
                $type = $property->getType();

                if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                    $candidate = $type->getName();
                    if (is_subclass_of($candidate, $baseModel)) {
                        return $candidate;
                    }
                }
            }
        } catch (Throwable) {
            // Silent failure - let guessing take over
        }

        return null;
    }
}