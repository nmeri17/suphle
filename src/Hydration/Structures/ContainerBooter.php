<?php

namespace Suphle\Hydration\Structures;

use Suphle\Hydration\Container;

class ContainerBooter
{
    public function __construct(protected readonly Container $container) {}

    public function initializeContainer(string $interfaceList): void
    {
        if ($this->container->hasEssentialsInited) return;

        $this->container->setEssentials();

        $this->container->setInterfaceHydrator($interfaceList);

        $this->container->interiorDecorate();

        $this->container->hasEssentialsInited = true;
    }
}
