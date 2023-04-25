<?php

namespace Suphle\Contracts\Hydration\ScopeHandlers;

interface ModifiesArguments
{
    /**
     * @param {arguments} Method argument list
    */
    public function transformConstructor(object $dummyInstance, array $arguments): array;

    /**
     * @param {arguments} Method argument list
    */
    public function transformMethods(object $concreteInstance, array $arguments, string $methodName): array;

    public function setAttributesList(array $attributes): void;
}
