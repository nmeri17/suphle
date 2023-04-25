<?php

namespace Suphle\Contracts\Hydration;

interface InterfaceCollection
{
    public function getLoaders(): array;

    public function simpleBinds(): array;

    public function delegateHydrants(array $interfaces): void;

    public function getDelegatedInstances(): array;

    public function getConfigs(): array;
}
