<?php

namespace Suphle\Routing\Structures;

use Suphle\Routing\Attributes\HttpMethod;

class RouteInfo
{
    private array $parameters = [];

    public function __construct(
        public readonly string $path,
        public readonly HttpMethod $method,
        public readonly string $controllerClass,
        public readonly string $controllerMethod,
        public readonly array $middlewares = [],
        public readonly ?CanaryInfo $canaryInfo = null,
        public readonly ?string $viewName = null
    ) {
        //
    }

    public function getFullPath(): string
    {
        return $this->path;
    }

    public function matches(string $requestPath, string $requestMethod): bool
    {
        return $this->method->value === strtoupper($requestMethod) && 
               $this->pathMatches($requestPath);
    }

    private function pathMatches(string $requestPath): bool
    {
        // Convert route pattern to regex
        $pattern = $this->convertPathToRegex($this->path);
        
        if (preg_match($pattern, $requestPath, $matches)) {
            // Extract parameters
            $this->extractParameters($matches);
            return true;
        }
        
        return false;
    }

    private function convertPathToRegex(string $path): string
    {
        // Replace {param} with regex capture groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        
        // Escape forward slashes and add start/end anchors
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    private function extractParameters(array $matches): void
    {
        // Extract parameter names from the original path
        preg_match_all('/\{([^}]+)\}/', $this->path, $paramNames);
        
        // Map parameter names to values
        for ($i = 0; $i < count($paramNames[1]); $i++) {
            $paramName = $paramNames[1][$i];
            $paramValue = $matches[$i + 1] ?? null;
            
            if ($paramValue !== null) {
                $this->parameters[$paramName] = $paramValue;
            }
        }
    }

    public function getParameter(string $name): ?string
    {
        return $this->parameters[$name] ?? null;
    }

    public function getAllParameters(): array
    {
        return $this->parameters;
    }
} 