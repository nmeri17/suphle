<?php
namespace Suphle\Routing\Structures;

use Suphle\Routing\Attributes\HttpMethod;

use Suphle\Request\SanitizesIntegerInput;

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton]
class RouteInfo
{
    use SanitizesIntegerInput;

    private array $parameters = [];

    public function __construct(
        public readonly string $path,
        public readonly HttpMethod $method,
        public readonly string $controllerClass,
        public readonly string $controllerMethod,
        public readonly array $preMiddlewares = [],
        public readonly array $middlewares = [],
        public readonly string $moduleName = "",
        public readonly ?array $canaryInfo = null,
        public readonly ?string $viewName = null,
        public readonly ?array $flows = null
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

    public function literalMatches(string $requestPath, string $requestMethod): bool
    {
        return $this->method->value === strtoupper($requestMethod) && 
        
        strtolower($requestPath) == strtolower($this->path);
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
    public function getPathFromStack(): string
    {
        $url = $this->path;

        foreach ($this->parameters as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }

        // Optional: Check if any placeholders remain unreplaced
        if (preg_match('/\{([^}]+)\}/', $url)) {
            throw new InvalidArgumentException("Missing parameters for route: $url");
        }

        return $url;
    }

    public function setSegmentValues(array $values): void
    {
        $this->parameters = $values;
    }

    public function getSegmentValue(string $name): ?string
    {

        return $this->parameters[$name] ?? null;
    }

    public function getAllSegmentValues(): array
    {

        return $this->parameters;
    }

    /**
     * Should be called before the readers start calling [getSegmentValue]
    */
    public function allNumericToPositive(): void
    {

        $this->parameters = $this->allInputToPositive($this->parameters);
    }

    public function clearAllSegments(): void
    {

        $this->parameters = [];

        $this->hasExchangedTokens = false; // since this object may be long-lived, without this, the placeholder stack won't be re-computed
    }
} 