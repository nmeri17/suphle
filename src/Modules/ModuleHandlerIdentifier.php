<?php

namespace Suphle\Modules;

use Suphle\Hydration\Container;

use Suphle\Hydration\Structures\{ContainerBooter, BaseInterfaceCollection};

use Suphle\Modules\Structures\ActiveDescriptors;

use Suphle\Flows\OuterFlowWrapper;

use Suphle\Contracts\Config\{Flows as FlowConfig};

use Suphle\Contracts\{ Auth\ModuleLoginHandler, Presentation\BaseRenderer, Response\RendererManager};

use Suphle\Contracts\Modules\{HighLevelRequestHandler, DescriptorInterface};

use Suphle\Events\ModuleLevelEvents;

use Suphle\Exception\Explosives\{ValidationFailure, NotFoundException};

use Suphle\Request\RequestDetails;

use Suphle\Routing\{AttributeRouteScanner, ModuleRequestRouter};

use Suphle\WebSockets\WebSocketRouter;

use Psr\Http\Message\ServerRequestInterface;

use Throwable;

abstract class ModuleHandlerIdentifier
{
    protected HighLevelRequestHandler $identifiedHandler;

    protected ?DescriptorInterface $routedModule = null;

    protected ?Container $container = null;

    protected array $descriptorInstances;

    protected ActiveDescriptors $descriptorsHolder;

    protected ?array $httpRoutes = null;

    public function __construct()
    {

        $this->setTitularContainer();
    }

    protected function setTitularContainer(): void
    {

        $this->descriptorInstances = $this->getModules();

        if (empty($this->descriptorInstances)) {
            return;
        }

        $this->descriptorsHolder = new ActiveDescriptors($this->descriptorInstances);

        $this->container = $this->descriptorsHolder->firstOriginalContainer();

        $this->container->setEssentials();
    }

    abstract protected function getModules(): array;

    public function bootModules(): void
    {
        $this->container->getClass(ModulesBooter::class)

        ->bootOuterModules($this->descriptorsHolder);

        $this->cacheAppRoutes();
    }

    public function cacheAppRoutes(): void {
            
        $this->httpRoutes = $this->container->getClass(AttributeRouteScanner::class)

        ->scanAllModules();

        $this->container->getClass(WebSocketRouter::class)->registerRoutes();
    }

    public function setRequestPath (
        string $requestPath, string $httpMethod = null, // when null, RequestDetails::deriveHttpMethod will detect it

        ServerRequestInterface $contextualRequest = null
    ): void
    {
        RequestDetails::setLoopInput($contextualRequest);

        RequestDetails::fromModules(
            $this->descriptorInstances,
            $requestPath,
            $httpMethod
        );
    }

    /**
     * @param {writeHeaders}:bool. When false, we assume response is not being outputted to browser or is piped to another process that will write them
    */
    public function diffuseSetResponse(bool $writeHeaders = true): void
    {

        $this->freshExceptionBridge()->epilogue();

        try {

            $this->respondFromHandler();
        } catch (Throwable $exception) {

            $this->findExceptionRenderer($exception);
        }

        if ($writeHeaders) {
            $this->transferHeaders();
        }
    }

    private function freshExceptionBridge(): ModuleExceptionBridge
    {

        return $this->getActiveContainer()->getClass(ModuleExceptionBridge::class);
    }

    /**
     * Each of the request handlers should update this class with the underlying renderer they're pulling a response from
    */
    public function respondFromHandler(): BaseRenderer
    {

        $container = $this->container;

        if ($container->getClass(FlowConfig::class)->isEnabled()) {

            $wrapper = $container->getClass(OuterFlowWrapper::class);

            if ($wrapper->canHandle()) {

                return $this->flowRequestHandler($wrapper);
            }
        }

        if ($routedRenderer = $this->handleGenericRequest()) {

            return $routedRenderer;
        }

        throw new NotFoundException();
    }

    public function handleGenericRequest(): ?BaseRenderer
    {
        $moduleRouter = $this->identifiedHandler = $this->container->getClass(ModuleRequestRouter::class);

        if (!$moduleRouter->canSetHandlingModule($this->httpRoutes)) return null;

        $this->routedModule = $moduleRouter->getActiveModule();

        return $moduleRouter->triggerInfoModule($this->descriptorsHolder);
    }

    public function flowRequestHandler(OuterFlowWrapper $wrapper): BaseRenderer
    {

        $this->identifiedHandler = $wrapper;

        $renderer = $wrapper->handlingRenderer();

        $wrapper->afterRender($renderer->render());

        $wrapper->emptyFlow();

        return $renderer;
    }

    public function findExceptionRenderer(Throwable $originalException): void
    {

        $this->identifiedHandler = $this->freshExceptionBridge(); // from currently active container after routing may have occured

        try {

            $this->identifiedHandler->hydrateHandler($originalException);
        }
        catch (Throwable $exception) { // if we can't hydrate the handler

            throw $originalException;
        }
    }

    public function underlyingRenderer(): BaseRenderer
    {

        return $this->identifiedHandler->handlingRenderer();
    }

    protected function transferHeaders(): void
    {

        $renderer = $this->underlyingRenderer();

        http_response_code($renderer->getStatusCode());

        foreach ($renderer->getHeaders() as $name => $value) {

            header("$name: $value");
        }
    }

    protected function getActiveContainer(): Container
    {

        if (!is_null($this->routedModule)) {

            return $this->routedModule->getContainer();
        }

        return $this->container;
    }

    public function firstContainer(): ?Container
    {

        return $this->container;
    }

    public function getAllRoutes(): array
    {
        return $this->httpRoutes;
    }

    public function getCachedRoutes(): ?array
    {
        return $this->httpRoutes;
    }
}
