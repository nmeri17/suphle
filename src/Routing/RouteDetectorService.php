<?php

namespace Suphle\Routing;

use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryState, HttpMethod, PreMiddleware};
use Suphle\Contracts\Presentation\BaseRenderer;
use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Analysis\RouteAnalysisService;
use Suphle\Flows\FlowHydrator;
use ReflectionClass;
use ReflectionMethod;

class RouteDetectorService extends RouteAnalysisService
{
    public function __construct(
        protected readonly RouterConfig $config,
        protected readonly Container $container,
        FlowHydrator $flowHydrator
    ) {
        parent::__construct($config, $container, $flowHydrator);
    }

    public function getAllRoutes(): array
    {
        $routeDetails = $this->compileRouteDetails();
        $allRoutes = [];
        
        foreach ($routeDetails as $coordinatorClass => $routes) {
            foreach ($routes as $route) {
                $allRoutes[] = $route;
            }
        }
        
        return $allRoutes;
    }

    public function getRoutesByModule(string $moduleName): array
    {
        $routeDetails = $this->compileRouteDetails();
        $moduleRoutes = [];
        
        foreach ($routeDetails as $coordinatorClass => $routes) {
            if (str_contains($coordinatorClass, $moduleName)) {
                foreach ($routes as $route) {
                    $moduleRoutes[] = $route;
                }
            }
        }
        
        return $moduleRoutes;
    }

    public function filterRoutes(array $routes, array $filters): array
    {
        return array_filter($routes, function ($route) use ($filters) {
            // Module filter
            if (isset($filters['module']) && !str_contains($route['coordinator'], $filters['module'])) {
                return false;
            }
            
            // Method filter
            if (isset($filters['method']) && strtoupper($route['method']) !== strtoupper($filters['method'])) {
                return false;
            }
            
            // Path filter
            if (isset($filters['path']) && !str_contains($route['path'], $filters['path'])) {
                return false;
            }
            
            return true;
        });
    }

    public function compileRouteDetails(array $skipPatterns = []): array
    {
        $routeDetails = [];
        $coordinatorClasses = $this->getCoordinatorClasses();

        foreach ($coordinatorClasses as $coordinatorClass) {
            $routeDetails[$coordinatorClass] = $this->analyzeCoordinator($coordinatorClass);
        }

        return $routeDetails;
    }

    protected function getCoordinatorRouteDetails(string $coordinatorClass): array
    {
        $reflection = new ReflectionClass($coordinatorClass);
        $routeDetails = [];

        // Get class-level attributes
        $routePrefix = $this->getRoutePrefix($reflection);
        $canaryState = $this->getCanaryState($reflection);
        $classPreMiddleware = $this->getPreMiddleware($reflection);

        // Get method-level routes
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttribute = $method->getAttributes(Route::class)[0] ?? null;
            
            if ($routeAttribute) {
                $routeArgs = $routeAttribute->getArguments();
                $routePattern = $routeArgs[0] ?? '';
                $httpMethod = $routeArgs[1] ?? HttpMethod::GET;
                $middleware = $routeArgs[2] ?? [];

                // Get method-level PreMiddleware (overrides class-level)
                $methodPreMiddleware = $this->getPreMiddleware($method);
                $preMiddleware = $methodPreMiddleware ?: $classPreMiddleware;

                $fullPath = $this->buildFullPath($routePrefix, $routePattern);
                
                $routeDetails[] = [
                    'method' => $httpMethod->value,
                    'path' => $fullPath,
                    'handler' => $method->getName(),
                    'middleware' => $middleware,
                    'pre_middleware' => $preMiddleware,
                    'canary_state' => $canaryState,
                    'placeholders' => $this->extractPlaceholders($routePattern),
                    'renderer' => $this->getReturnType($method),
                    'coordinator' => $coordinatorClass
                ];
            }
        }

        return $routeDetails;
    }

    protected function getRoutePrefix(ReflectionClass $reflection): string
    {
        $prefixAttributes = $reflection->getAttributes(RoutePrefix::class);
        
        if (!empty($prefixAttributes)) {
            $prefixArgs = $prefixAttributes[0]->getArguments();
            return $prefixArgs[0] ?? '';
        }

        return '';
    }

    protected function getCanaryState(ReflectionClass $reflection): ?array
    {
        $canaryAttributes = $reflection->getAttributes(CanaryState::class);
        
        if (!empty($canaryAttributes)) {
            $canaryArgs = $canaryAttributes[0]->getArguments();
            return $canaryArgs[0] ?? null;
        }

        return null;
    }

    protected function getPreMiddleware($reflection): ?string
    {
        $preMiddlewareAttributes = $reflection->getAttributes(PreMiddleware::class);
        
        if (!empty($preMiddlewareAttributes)) {
            $preMiddlewareArgs = $preMiddlewareAttributes[0]->getArguments();
            return $preMiddlewareArgs[0] ?? null;
        }

        return null;
    }

    protected function buildFullPath(string $prefix, string $pattern): string
    {
        $path = $prefix;
        
        if (!empty($path) && !str_starts_with($pattern, '/')) {
            $path .= '/';
        }
        
        $path .= $pattern;
        
        // Ensure path starts with /
        if (!str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        
        return $path;
    }

    protected function extractPlaceholders(string $pattern): array
    {
        $placeholders = [];
        preg_match_all('/\{([^}]+)\}/', $pattern, $placeholders);
        
        if (!empty($placeholders[1])) {
            foreach ($placeholders[1] as $placeholder) {
                $placeholders[] = $placeholder;
            }
        }
        
        return $placeholders;
    }

    protected function getReturnType(ReflectionMethod $method): ?string
    {
        $returnType = $method->getReturnType();
        
        if ($returnType) {
            return $returnType->getName();
        }
        
        return null;
    }
} 