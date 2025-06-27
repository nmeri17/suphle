<?php

namespace Suphle\Routing;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Request\RequestDetails;
use Suphle\Routing\Structures\{RouteInfo, CanaryInfo};
use Suphle\Services\Decorators\BindsAsSingleton;
use Suphle\Modules\ModuleHandlerIdentifier;

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
            $controller = $this->evaluateCanary($route->canaryInfo, $controller);
        }
        
        // Execute the controller method
        return $controller->{$route->controllerMethod}();
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

    private function evaluateCanary(CanaryInfo $canaryInfo, object $controller): object
    {
        foreach ($canaryInfo->evaluators as $evaluatorClass) {
            $evaluator = $this->container->getClass($evaluatorClass);
            
            if (method_exists($evaluator, 'shouldUseCanary') && $evaluator->shouldUseCanary()) {
                return $this->container->getClass($canaryInfo->fallback);
            }
        }
        
        return $controller;
    }

    private function getCoordinatorDirectories(): array
    {
        $coordinatorPath = $this->config->getCoordinatorPath();
        $directories = [];
        
        // Get the module handler to find all modules
        $moduleHandler = $this->container->getClass(ModuleHandlerIdentifier::class);
        $modules = $moduleHandler->getModules();
        
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
                $coordinatorDir = $moduleRoot . $coordinatorPath;
                
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
        $versionsDir = $moduleRoot . $coordinatorPath . '/Versions';
        if (is_dir($versionsDir)) {
            $versionDirs = glob($versionsDir . '/V*', GLOB_ONLYDIR);
            // Sort in descending order (newest first)
            rsort($versionDirs);
            $directories = array_merge($directories, $versionDirs);
        }
        
        // Add base coordinator directory for fallback
        $baseDir = $moduleRoot . $coordinatorPath;
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

    /**
     * Get the mirror authenticator class name for authentication handling
     */
    public function getMirrorAuthenticator(): string
    {
        return $this->config->mirrorAuthenticator();
    }
} 