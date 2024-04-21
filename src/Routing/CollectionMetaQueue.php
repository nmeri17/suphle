<?php

namespace Suphle\Routing;

use Suphle\Routing\Structures\BaseScrutinizerHandler;

use Suphle\Hydration\Container;

use Suphle\Contracts\{Presentation\BaseRenderer, Config\Router as RouterConfig};

class CollectionMetaQueue
{
    public function __construct(
        protected readonly PreMiddlewareRegistry $registry,
        protected readonly RouterConfig $routerConfig,
        protected readonly Container $container
    ) {

        //
    }

    public function executeRoutedMetaFunnels(): void
    {

        $this->executeMetaFunnels($this->registry->getFunnelsForInteracted());
    }

    public function executeMetaFunnels(array $funnels): void
    {

        $handlers = [];

        foreach ($funnels as $funnel) {

            $handlerName = $this->routerConfig->scrutinizerHandlers()[

                $funnel::class
            ];

            if (!array_key_exists($handlerName, $handlers)) {

                $handlers[$handlerName] = $this->container->getClass($handlerName);
            }

            $handlers[$handlerName]->addMetaFunnel($funnel); // funnels represent different contexts of information/detail passable to the same handler
        }

        array_walk(
            $handlers,
            fn (BaseScrutinizerHandler $handler) => $handler->scrutinizeRequest() // we have deferred scrutiny until outside above loop so the filters execute only once
        );
    }

    public function findRoutedFunnels(callable $matcher, array $interactedPatterns = null): array
    {

        return array_filter(
        	$this->registry->getFunnelsForInteracted($interactedPatterns),

        	$matcher
        );
    }
}
