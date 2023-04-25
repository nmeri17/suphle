<?php

namespace Suphle\Services\DecoratorHandlers;

use Suphle\Contracts\Hydration\ScopeHandlers\ModifyInjected;

use Suphle\Hydration\{Container, Structures\ObjectDetails};

use Suphle\Services\Structures\SetsReflectionAttributes;

use Suphle\Exception\Explosives\DevError\InvalidImplementor;

class BindSingletonHandler implements ModifyInjected
{
    use SetsReflectionAttributes;

    public function __construct(
        protected readonly ObjectDetails $objectMeta,
        protected readonly Container $container
    ) {

        //
    }

    public function examineInstance(object $concrete, string $caller): object
    {

        $attribute = end($this->attributesList)->newInstance();

        $concreteName = $concrete::class;

        $allegedParent = $attribute->entityIdentity ?? $concreteName;

        if (!$this->objectMeta->stringInClassTree(
            $concreteName,
            $allegedParent
        )) {

            throw new InvalidImplementor($allegedParent, $concreteName);
        }

        $this->container->whenTypeAny()->needsAny([

            $allegedParent => $concrete
        ]);

        return $concrete;
    }
}
