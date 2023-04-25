<?php

namespace Suphle\Hydration;

use Suphle\Contracts\Hydration\DecoratorChain;

use Suphle\Contracts\Hydration\ScopeHandlers\{ModifiesArguments, ModifyInjected};

use Suphle\Hydration\Structures\ObjectDetails;

use Suphle\Services\Decorators\BindsAsSingleton;

class DecoratorHydrator
{
    protected array $allScopes;
    protected array $argumentScope;
    protected array $injectScope;

    public function __construct(
        protected readonly Container $container,
        DecoratorChain $allScopes,
        protected readonly ObjectDetails $objectMeta
    ) {

        $this->allScopes = $allScopes->allScopes();

        $this->assignScopes();
    }

    public function assignScopes(): void
    {

        $this->argumentScope = array_filter($this->allScopes, function ($handler) {

            return $this->objectMeta->implementsInterface(
                $handler,
                ModifiesArguments::class
            );
        });

        $this->injectScope = array_filter($this->allScopes, function ($handler) {

            return $this->objectMeta->implementsInterface(
                $handler,
                ModifyInjected::class
            );
        });
    }

    public function scopeArguments(string $entityName, array $argumentList, string $methodName): array
    {

        $scope = $this->argumentScope;

        $container = $this->container;

        $relevantDecors = $this->getRelevantDecors($scope, $entityName);

        if (empty($relevantDecors)) {
            return $argumentList;
        }

        $hasConstructor = false;

        if ($methodName == Container::CLASS_CONSTRUCTOR) {

            $hasConstructor = true;

            $concrete = $this->objectMeta->noConstructor($entityName);
        } else {
            $concrete = $container->getClass($entityName);
        }

        foreach ($relevantDecors as $decoratorName => $attributes) {

            $handler = $container->getClass($scope[$decoratorName]);

            $handler->setAttributesList($attributes);

            if ($hasConstructor) {

                $argumentList = $handler->transformConstructor($concrete, $argumentList);
            } else {
                $argumentList = $handler->transformMethods($concrete, $argumentList, $methodName);
            }
        }

        return $argumentList;
    }

    /**
     * @return Array. Relevant decorators and active attributes
    */
    public function getRelevantDecors(array $context, string $search): array
    {

        $attributes = $this->objectMeta->getClassAttributes($search);

        $attributeToHandler = [];

        foreach ($context as $decoratorName => $handler) {

            foreach ($attributes as $attribute) {

                if ($attribute->getName() == $decoratorName) {

                    $attributeToHandler[$decoratorName][] = $attribute;
                }
            }
        }

        return $attributeToHandler;
    }

    public function scopeInjecting(object $concrete, string $caller)
    {

        $scope = $this->injectScope;

        $relevantDecors = $this->getRelevantDecors($scope, $concrete::class);

        foreach ($relevantDecors as $decorator => $attributes) {

            $handler = $this->container->getClass($scope[$decorator]);

            $handler->setAttributesList($attributes);

            $concrete = $handler->examineInstance($concrete, $caller);
        }

        return $concrete;
    }
}
