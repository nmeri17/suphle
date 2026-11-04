<?php
namespace Suphle\Response;

use Suphle\Hydration\{Container, DecoratorHydrator};

use Suphle\Services\Decorators\{BindsAsSingleton};

use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

use Suphle\Contracts\Response\{BaseResponseManager, RendererManager};

use Suphle\Response\Format\Redirect;

use Suphle\Request\{ PayloadStorage, RequestDetails};

use Suphle\Routing\Structures\RouteInfo;

#[BindsAsSingleton(RendererManager::class)]
class RoutedRendererManager implements RendererManager, BaseResponseManager
{

    public const PREVIOUS_GET_URL = "PREVIOUS_GET_URL";

    protected BaseRenderer $renderer;

    public function __construct(
        protected readonly Container $container,
        protected readonly Session $sessionClient,
        protected readonly FlowResponseQueuer $flowQueuer,
        protected readonly RequestDetails $requestDetails,
        protected readonly RouteInfo $routeDetails
    ) {}

    public function responseRenderer(): BaseRenderer
    {

        return $this->renderer;
    }

    public function afterRender($data = null): void
    {

        if (!is_null($this->routeDetails->flows)) {// the first organic request needs to trigger the flows below it

            $this->flowQueuer->saveSubBranches($this->renderer, $this->routeDetails);
        }

        if ($this->shouldStoreRenderer())

            $this->sessionClient->resetOldInput();
    }

    protected function setCanaryState():void
    {
        $canaryList = $this->routeDetails->canaryInfo; // this would have been better impl on requestDetails for it to be read lazily. unfortunately, that would require passing a container as well for canary hydration, which is beyond his scope

        if (is_null($canaryList)) return;

        foreach ($canaryList as $canary) {

            $loadVal = $this->container->getClass($canary)->willLoad();

            if (!is_null($loadVal))

                $this->requestDetails->setCanaryState($loadVal);
        }
    }

    public function handleValidRequest(PayloadStorage $payloadStorage): BaseRenderer
    {
        $this->setCanaryState();

        if ($this->shouldStoreRenderer()) {

            $this->sessionClient->setValue(
                self::PREVIOUS_GET_URL,
                $this->requestDetails->getPath()
            );
        }

        $controllerMethod = $this->routeDetails->controllerMethod;

        $className = $this->routeDetails->controllerClass;

        return $this->renderer = call_user_func_array(
            [
                $this->container->getClass($className), $controllerMethod
            ],
            $this->container->getMethodParameters($controllerMethod, $className)
        );
    }

    protected function shouldStoreRenderer(): bool
    {
        return $this->requestDetails->isGetRequest() &&

        !$this->requestDetails->isApiRoute();
    }

    /**
     * Checks for whether current or previous should be rendered, depending on currently active renderer
     *
     * Expects current request to contain same placeholder values used for executing that preceding request
    */
    public function invokePreviousRenderer(array $toMerge = []): BaseRenderer
    {

        if (!$this->renderer->deferValidationContent()) {// if current request is something like json, write validation errors to it

            $previousRenderer = $this->renderer;

            $previousRenderer->forceArrayShape($toMerge);
        } else {

            $previousRenderer = new Redirect(fn () => $this->getPreviousUrl());

            foreach ($toMerge as $key => $value) {

                $this->sessionClient->setFlashValue($key, $value);
            }

            $this->scopeRenderer($previousRenderer);
        }

        return $previousRenderer;
    }
    protected function scopeRenderer (BaseRenderer $renderer):void {

        $this->container->getClass(DecoratorHydrator::class)->scopeInjecting($renderer, self::class);
    }
    public function getPreviousUrl ():string {

        return $this->sessionClient->getValue(self::PREVIOUS_GET_URL);
    }
}
