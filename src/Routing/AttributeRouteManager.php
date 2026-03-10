<?php

namespace Suphle\Routing;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Request\RequestDetails;
use Suphle\Routing\Structures\{RouteInfo, CanaryInfo};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Services\Decorators\BindsAsSingleton;
use Suphle\Modules\Structures\ActiveDescriptors;

#[BindsAsSingleton]
class AttributeRouteManager
{
    /** @var RouteInfo[] */
    private array $routes = [];
    
    private bool $routesScanned = false;

    public function __construct(
        private readonly RouterConfig $config,
        private readonly Container $container,
        private readonly RequestDetails $requestDetails,
        private readonly AttributeRouteScanner $scanner
    ) {
        //
    }

    public function scanAndRegisterRoutes(): void
    {
        if ($this->routesScanned) {
            return;
        }

        $coordinatorDirectories = $this->getCoordinatorDirectories();
        $this->routes = $this->scanner->scanRoutes($coordinatorDirectories, $this->config->getCoordinatorClassesToScan());
        $this->routesScanned = true;
    }

    public function findRoute(string $path, string $method): ?RouteInfo
    {
        $this->scanAndRegisterRoutes();

        foreach ($this->routes as $route) {
            if ($route->matches($path, $method)) {
                return $route;
            }
        }

        return null;
    }

    public function dispatchRoute(RouteInfo $route): mixed
    {
        // Set route parameters in PathPlaceholders
        $this->setRouteParameters($route);
        
        // Create controller instance
        $controller = $this->container->getClass($route->controllerClass);
        
        // Apply middlewares
        $this->applyMiddlewares($route->middlewares);
        
        // Check canary conditions
        if ($route->canaryInfo) {

            $this->evaluateCanary($route->canaryInfo);
        }
        
        // Execute the controller method
        $renderer = $controller->{$route->controllerMethod}();

        if ($renderer instanceof BaseRenderer) {

            $renderer->setCoordinatorClass($controller);

            $renderer->setHandler($route->controllerMethod);
        }

        return $renderer;
    }

    private function setRouteParameters(RouteInfo $route): void
    {
        $pathPlaceholders = $this->container->getClass(\Suphle\Routing\PathPlaceholders::class);
        
        // Set the extracted parameters
        foreach ($route->getAllParameters() as $name => $value) {
            $pathPlaceholders->foundSegments([$name => $value]);
        }
    }

    private function applyMiddlewares(array $middlewareClasses): void
    {
        foreach ($middlewareClasses as $middlewareClass) {
            $middleware = $this->container->getClass($middlewareClass);
            
            if (method_exists($middleware, 'handle')) {
                $middleware->handle($this->requestDetails);
            }
        }
    }

    private function evaluateCanary(CanaryInfo $canaryInfo): void
    {
        foreach ($canaryInfo->evaluators as $evaluatorClass) {

            $evaluator = $this->container->getClass($evaluatorClass);
            
            $result = $evaluator->willLoad();

            if (!is_null($result)) {

                $this->requestDetails->setCanaryState($result);

                return;
            }
        }
    }

    private function getCoordinatorDirectories(): array
    {
        $coordinatorPath = $this->config->getCoordinatorPath();
        $directories = [];
        
        // Get ActiveDescriptors to find all modules
        $activeDescriptors = $this->container->getClass(ActiveDescriptors::class);
        $modules = $activeDescriptors->getOriginalDescriptors();
        
        foreach ($modules as $module) {
            // Get the module's ModuleFiles instance to get the module root path
            $moduleContainer = $module->getContainer();
            $moduleFiles = $moduleContainer->getClass(\Suphle\Contracts\Config\ModuleFiles::class);
            $moduleRoot = $moduleFiles->activeModulePath();
            
            // Handle API versioning if this is an API request
            if ($this->requestDetails->isApiRoute()) {
                $directories = array_merge($directories, $this->getApiVersionDirectories($moduleRoot, $coordinatorPath));
            } else {
                // Build the coordinator directory path for browser routes
                $coordinatorDir = rtrim($moduleRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($coordinatorPath, DIRECTORY_SEPARATOR);
                
                if (is_dir($coordinatorDir)) {
                    $directories[] = $coordinatorDir;
                }
            }
        }
        
        return $directories;
    }

    private function getApiVersionDirectories(string $moduleRoot, string $coordinatorPath): array
    {
        $directories = [];
        
        // Scan for version directories automatically
        $versionsDir = rtrim($moduleRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 
                       trim($coordinatorPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Versions';
        if (is_dir($versionsDir)) {
            $versionDirs = glob($versionsDir . DIRECTORY_SEPARATOR . 'V*', GLOB_ONLYDIR);
            // Sort in descending order (newest first)
            rsort($versionDirs);
            $directories = array_merge($directories, $versionDirs);
        }
        
        // Add base coordinator directory for fallback
        $baseDir = rtrim($moduleRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . trim($coordinatorPath, DIRECTORY_SEPARATOR);
        if (is_dir($baseDir)) {
            $directories[] = $baseDir;
        }
        
        return $directories;
    }

    /**
     * @return RouteInfo[]
     */
    public function getAllRoutes(): array
    {
        $this->scanAndRegisterRoutes();
        return $this->routes;
    }

    public function hasRoutes(): bool
    {
        $this->scanAndRegisterRoutes();
        return !empty($this->routes);
    }

    /**
     * Get the mirror authenticator class name for authentication handling
     */
    public function getMirrorAuthenticator(): string
    {
        return $this->config->mirrorAuthenticator();
    }
} 