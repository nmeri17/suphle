<?php
namespace Suphle\Services\DependencyRules;

use Suphle\Services\Decorators\DomainService;

use Suphle\Exception\Explosives\DevError\UnacceptableDependency;

class ServicePreferenceHandler extends BaseDependencyHandler
{
    public function evaluateClass(string $className): void
    {

        foreach ($this->constructorDependencyTypes($className) as $dependencyType) {

            // 1. Check if it's a hardcoded core class (Session, CSRF, etc.)
            if ($this->isPermittedParent($this->argumentList, $dependencyType)) {
                continue;
            }

            // 2. Check if the dependency is a POPO marked as a DomainService
            $attributes = $this->objectMeta->getClassAttributes(
                $dependencyType, 
                DomainService::class
            );

            if (empty($attributes)) {
                throw new UnacceptableDependency(
                    $className,
                    $dependencyType
                );
            }
        }
    }
}