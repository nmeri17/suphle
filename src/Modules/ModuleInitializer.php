<?php

namespace Suphle\Modules;


use Suphle\Routing\AttributeRouteManager;

use Suphle\Request\{RequestDetails, PayloadStorage};

use Suphle\Hydration\{Container, DecoratorHydrator};
use Suphle\Routing\Structures\RouteInfo;

use Suphle\Contracts\{Presentation\BaseRenderer, Response\RendererManager};

use Suphle\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

use Suphle\Exception\Explosives\{UnauthorizedServiceAccess, Unauthenticated};

class ModuleInitializer implements HighLevelRequestHandler
{
    protected bool $foundRoute = false;

    protected readonly Container $container;

    protected ?BaseRenderer $finalRenderer = null;

    protected ?RouteInfo $activeRoute = null;

    public function __construct(
        protected readonly DescriptorInterface $descriptor,
        protected readonly RequestDetails $requestDetails,
        protected readonly DecoratorHydrator $decoratorHydrator,
        protected readonly AttributeRouteManager $router
    ) {

        $this->container = $descriptor->getContainer();
    }

    public function assignRoute(): self
    {

        $route = $this->router->findRoute(
            $this->requestDetails->getPath(),
            $this->requestDetails->getHttpMethod()
        );

        if ($route) {

            $this->foundRoute = true;

            $this->activeRoute = $route;
            
            $this->bindRoutingSideEffects();
        }

        return $this;
    }

    protected function bindRoutingSideEffects(): void
    {

        $renderer = $this->finalRenderer;

        /**
         * Not really necessary but just a slight optimization to save callers from demeter on the router.
         *
         * Any of those callers should assume its module has routed to a renderer
         *
         * Ordering here binds to container before scoping the renderer, in case any of the dependencies requires the renderer itself
        */
        $this->container->whenTypeAny()->needsAny([

            BaseRenderer::class => function () {

                if (is_null($this->finalRenderer)) {

                    $this->setHandlingRenderer();
                }

                return $this->finalRenderer;
            }
        ]);
    }

    /**
     * @param {rendererManager} this manager should come from currently active module
     *
     * @throws ValidationFailure
    */
    public function fullRequestProtocols(RendererManager $rendererManager): self
    {


        $rendererManager->mayBeInvalid()->bootDefaultRenderer();

        return $this;
    }

    public function setHandlingRenderer(): void
    {

        $rendererManager = $this->container->getClass(RendererManager::class);

        $this->finalRenderer = $rendererManager->handleValidRequest(
            $this->container->getClass(PayloadStorage::class)
        );
    }

    public function handlingRenderer(): ?BaseRenderer
    {

        return $this->finalRenderer;
    }

    public function didFindRoute(): bool
    {

        return $this->foundRoute;
    }
}
