<?php

namespace Suphle\Routing;

use ReflectionClass;
use ReflectionMethod;
use Suphle\Routing\Attributes\{Route, RoutePrefix, CanaryRoute, HttpMethod};
use Suphle\Routing\Structures\{RouteInfo, CanaryInfo};
use Suphle\Exception\Explosives\NotFoundException;

class AttributeRouteScanner
{
    /**
     * @param string[] $coordinatorDirectories
     * @param string[] $coordinatorClassesToScan Array of coordinator class names to scan, or empty array to scan all
     * @return RouteInfo[]
     */
    public function scanRoutes(array $coordinatorDirectories, array $coordinatorClassesToScan = []): array
    {
        $routes = [];
        
        foreach ($coordinatorDirectories as $directory) {
            $routes = array_merge($routes, $this->scanDirectory($directory, $coordinatorClassesToScan));
        }
        
        return $routes;
    }

    private function scanDirectory(string $directory, array $coordinatorClassesToScan = []): array
    {
        $routes = [];
        $files = glob($directory . '/*.php');
        
        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className && $this->shouldScanClass($className, $coordinatorClassesToScan)) {
                $routes = array_merge($routes, $this->scanClass($className));
            }
        }
        
        return $routes;
    }

    private function shouldScanClass(string $className, array $coordinatorClassesToScan): bool
    {
        // If no specific classes are specified, scan all
        if (empty($coordinatorClassesToScan)) {
            return true;
        }
        
        // Extract just the class name without namespace
        $shortClassName = basename(str_replace('\\', '/', $className));
        
        // Check if this class should be scanned
        return in_array($shortClassName, $coordinatorClassesToScan);
    }

    private function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];
            $className = basename($file, '.php');
            return $namespace . '\\' . $className;
        }
        
        return null;
    }

    private function scanClass(string $className): array
    {
        $routes = [];
        $reflection = new ReflectionClass($className);
        
        // Get class prefix - only from the class itself, not parent classes
        $prefix = $this->getClassPrefix($reflection);
        
        // If no RoutePrefix is found, ignore this class entirely
        if ($prefix === null) {
            return [];
        }
        
        // Scan methods for routes
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttributes = $method->getAttributes(Route::class);
            
            foreach ($routeAttributes as $routeAttribute) {
                $route = $routeAttribute->newInstance();
                $canaryInfo = $this->getCanaryInfo($method);
                
                $fullPath = $prefix ? $prefix . $route->path : $route->path;
                
                $routes[] = new RouteInfo(
                    path: $fullPath,
                    method: $route->method,
                    controllerClass: $className,
                    controllerMethod: $method->getName(),
                    middlewares: $route->middlewares,
                    canaryInfo: $canaryInfo
                );
            }
        }
        
        return $routes;
    }

    private function getClassPrefix(ReflectionClass $reflection): ?string
    {
        // Only check the class itself, not parent classes
        $prefixAttributes = $reflection->getAttributes(RoutePrefix::class);
        
        if (!empty($prefixAttributes)) {
            $prefixAttribute = $prefixAttributes[0]->newInstance();
            return $prefixAttribute->prefix;
        }
        
        // No RoutePrefix found on this class - return null to ignore it
        return null;
    }

    private function getCanaryInfo(ReflectionMethod $method): ?CanaryInfo
    {
        $canaryAttributes = $method->getAttributes(CanaryRoute::class);
        
        if (!empty($canaryAttributes)) {
            $canaryAttribute = $canaryAttributes[0]->newInstance();
            return new CanaryInfo(
                evaluators: $canaryAttribute->evaluators,
                fallback: $canaryAttribute->fallback
            );
        }
        
        return null;
    }
} 