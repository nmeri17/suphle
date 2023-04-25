<?php

namespace Suphle\Services\DependencyRules;

use Suphle\Contracts\Server\DependencyFileHandler;

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use ReflectionMethod;

abstract class BaseDependencyHandler implements DependencyFileHandler
{
    protected array $argumentList;

    public function __construct(
        protected readonly Container $container,
        protected readonly ObjectDetails $objectMeta
    ) {

        //
    }

    public function setRunArguments(array $argumentList): void
    {

        $this->argumentList = $argumentList;
    }

    /**
     * @param {dependency} mixed. Can be any type passed as argument
    */
    protected function isPermittedParent(array $parentList, $dependencyType): bool
    {

        foreach ($parentList as $typeToMatch) {

            if ($this->objectMeta->stringInClassTree(
                $dependencyType,
                $typeToMatch
            )) {
                return true;
            }
        }

        return false;
    }

    protected function constructorDependencyTypes(string $className): array
    {

        if (!method_exists($className, Container::CLASS_CONSTRUCTOR)) {

            return [];
        }

        return $this->methodDependencyTypes($className, Container::CLASS_CONSTRUCTOR);
    }

    protected function methodDependencyTypes(string $className, string $methodName): array
    {

        $reflectedCallable = new ReflectionMethod($className, $methodName);

        $noBuiltIn = array_filter($reflectedCallable->getParameters(), function ($parameter) {

            $hasType = $parameter->getType();

            return $hasType && !$hasType->isBuiltin();
        });

        return array_map(function ($parameter) {

            return $parameter->getType()->getName();
        }, $noBuiltIn);
    }
}
