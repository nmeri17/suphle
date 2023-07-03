<?php

namespace Suphle\Services\DependencyRules;

use Suphle\Hydration\Container;

use Suphle\Exception\Explosives\DevError\UnacceptableDependency;

/**
 * For each class, if argument 0 is spotted in its dependency list but the class is not in argument 1. Its fate is decided by argument 3, defaulting to false ie only loaders outside given list will result in an error
*/
class OnlyLoadedByHandler extends BaseDependencyHandler
{
    public function evaluateClass(string $className): void
    {

        $shouldPermitLoad = $this->argumentList[2] ?? false;

        foreach ($this->constructorDependencyTypes($className) as $dependencyType) {

            $toEvaluate = $this->objectMeta->stringInClassTree(
        
                $dependencyType, $this->argumentList[0]
            );

            if (!$toEvaluate) continue;

            $dependencyMatchesList = $this->isPermittedParent(

                $this->argumentList[1], $className
            );

            if ($dependencyMatchesList === $shouldPermitLoad) {

                throw new UnacceptableDependency(
                    $className,
                    $dependencyType
                );
            }
        }
    }
}
