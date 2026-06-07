<?php
namespace Suphle\Flows\Jobs;

use Suphle\Modules\{ModulesBooter, Structures\ActiveDescriptors};
use Suphle\Flows\{FlowHydrator, Structures\PendingFlowDetails};
use Suphle\Routing\{AttributeRouteScanner, ModuleRequestRouter, Attributes\FlowDefinition, Attributes\HttpMethod};
use Suphle\Hydration\Container;
use Suphle\Request\RequestDetails;
use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;
use Suphle\Contracts\{Queues\Task, IO\CacheManager, Modules\DescriptorInterface};
use ReflectionMethod, ReflectionAttribute;

class RouteBranches implements Task
{
    final public const FLOW_MECHANISMS = "flow_wildcards";

    protected array $httpRoutes;

    protected Container $container;

    protected DescriptorInterface $activeModule;

    protected ModuleRequestRouter $moduleRouter;

    public function __construct(
        protected readonly PendingFlowDetails $flowDetails,
        protected readonly ModulesBooter $modulesBooter,
        protected readonly ActiveDescriptors $descriptorsHolder,
        protected readonly CacheManager $cacheManager
    ) {}

    public function handle(): void
    {
        $routeDetails = $this->flowDetails->routeDetails;

        // V2: Get attributes instead of iterating $outgoingRenderer->getFlow()
        $reflection = new ReflectionMethod($routeDetails->controllerClass, $routeDetails->controllerMethod);

        $attributes = $reflection->getAttributes(FlowDefinition::class, ReflectionAttribute::IS_INSTANCEOF);

        $this->modulesBooter->bootOuterModules($this->descriptorsHolder);
            
        $this->container = $this->descriptorsHolder->getOriginalDescriptors()->getContainer();

        $this->httpRoutes = $this->container->getClass(AttributeRouteScanner::class)->scanAllModules();

        foreach ($attributes as $attr) {
            $flowInstance = $attr->newInstance();
            $urlPattern = $flowInstance->target;

            $mechanismPath = $this->getMechanismPath($urlPattern);
            if (!$this->patternMatchesMechanism($mechanismPath)) {
                continue;
            }

            if (!$this->findManagerForPattern($urlPattern)) {
                continue; 
            }

            $this->executeFlowBranch($flowInstance);
        }
    }

    protected function patternMatchesMechanism(string $mechanismPath): bool
    {
        $patternMechanism = $this->cacheManager->getItem($mechanismPath);

        if (is_null($patternMechanism)) {
            $this->cacheManager->saveItem($mechanismPath, $this->flowDetails->getAuthStorage());
            return true;
        }
        return $patternMechanism == $this->flowDetails->getAuthStorage();
    }

    protected function getMechanismPath(string $urlPattern): string
    {
        return self::FLOW_MECHANISMS . "/" . trim($urlPattern, "/");
    }

    private function findManagerForPattern(string $pattern): bool
    {
        $modules = $this->descriptorsHolder->getOriginalDescriptors();

        RequestDetails::fromModules($modules, $pattern, HttpMethod::GET->value);

        $this->moduleRouter = $this->container->getClass(ModuleRequestRouter::class);

        if (!$moduleRouter->canSetHandlingModule($this->httpRoutes, true))

            return false;

        $this->activeModule = $this->moduleRouter->getActiveModule();

        return true;
    }

    private function executeFlowBranch(FlowDefinition $flowInstance): void
    {
        $container = $this->activeModule->getContainer();

        $hydrator = $container->getClass(FlowHydrator::class);

        $hydrator->setRequestDetails(
            $this->flowDetails->getRenderer()->getRawResponse(),
            
            $this->moduleRouter->getFoundRoute()
        );

        $this->setHydratorDependencies($hydrator, $container);

        // V2: Replacing runNodes with runAttribute
        $hydrator->runAttribute($flowInstance, $this->flowDetails);
    }

    private function setHydratorDependencies(FlowHydrator $hydrator, Container $container): void
    {
        $handler = $container->getClass(VariableDependenciesHandler::class);

        foreach ($hydrator->dependencyMethods() as $methodName) {
            $handler->executeDependencyMethod($methodName, $hydrator);
        }
    }
}