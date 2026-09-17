<?php
namespace Suphle\Services\DecoratorHandlers;

use Suphle\Services\{BaseCoordinator, Structures\SetsReflectionAttributes, Decorators\VariableDependencies};

use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Exception\Explosives\DevError\UnacceptableDependency;

class VariableDependenciesHandler implements ModifyInjected
{
    use SetsReflectionAttributes;

    public function __construct(
        protected readonly Container $container,
        protected readonly ObjectDetails $objectMeta
    ) { }

    public function examineInstance(object $concrete, string $caller): object
    {
        $concreteName = $concrete::class;

        if ($this->objectMeta->stringInClassTree($concreteName, BaseCoordinator::class))
            throw new UnacceptableDependency($concreteName, VariableDependencies::class);

        foreach ($this->attributesList as $attributeMeta) {

            foreach (

                $attributeMeta->newInstance()->dependencyMethods as

                $methodName
            ) {

                $this->executeDependencyMethod($methodName, $concrete);
            }
        }

        return $concrete;
    }

    public function executeDependencyMethod(string $methodName, object $concrete): void
    {

        $concreteName = $concrete::class;

        $parameters = $this->container->getMethodParameters(
            $methodName,
            $concreteName,
            [$concreteName]
        );

        call_user_func_array([$concrete, $methodName], $parameters);
    }
}
