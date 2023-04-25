<?php

namespace Suphle\Services\DependencyRules;

use InvalidArgumentException;

class ActionDependenciesValidator extends BaseDependencyHandler
{
    public function evaluateClass(string $className): void
    {

        foreach ($this->objectMeta->getPublicMethods($className) as $methodName) {

            foreach (
                $this->methodDependencyTypes($className, $methodName) as $dependencyType
            ) {

                if (!$this->isPermittedParent($this->argumentList, $dependencyType)) {

                    throw new InvalidArgumentException(
                        $this->getErrorMessage(
                            $className,
                            $dependencyType,
                            $methodName
                        )
                    );
                }
            }
        }
    }

    protected function getErrorMessage(string $consumer, string $dependency, string $methodName): string
    {

        return $consumer . "::". $methodName .

        " is forbidden from depending on $dependency";
    }
}
