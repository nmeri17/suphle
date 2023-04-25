<?php

namespace Suphle\Routing;

use Suphle\Contracts\{Config\Router as RouterConfig, Presentation\BaseRenderer, Routing\ExternalRouter};

use Suphle\Hydration\Container;

class ExternalRouteMatcher
{
    protected ?ExternalRouter $activeHandler = null;

    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container
    ) {

        //
    }

    public function shouldDelegateRouting(): bool
    {

        foreach ($this->config->externalRouters() as $manager) {

            $instance = $this->container->getClass($manager);

            if ($instance->canHandleRequest()) {

                $this->activeHandler = $instance; // assumes that router has booted properly

                return true;
            }
        }

        return false;
    }

    public function hasActiveHandler(): bool
    {

        return !is_null($this->activeHandler);
    }

    public function getConvertedRenderer(): BaseRenderer
    {

        return $this->activeHandler->convertToRenderer();
    }
}
