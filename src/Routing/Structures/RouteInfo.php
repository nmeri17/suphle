<?php
namespace Suphle\Routing\Structures;

use Suphle\Routing\Attributes\HttpMethod;

use Suphle\Request\SanitizesIntegerInput;

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton]
class RouteInfo
{
    use SanitizesIntegerInput;

    public const PLACEHOLDER_PATTERN = "/\{([^}]+)\}/";

    private array $parameters = [];
    
    public bool $isMirror = false;

    public function __construct(
        public readonly string $path, // this value is being reset to its computed equivalent after the objects pass through route harvester and the array is rehydrated into routeInfos
        public readonly HttpMethod $method,
        public readonly string $controllerClass,
        public readonly string $controllerMethod,
        public readonly array $preMiddlewares = [],
        public readonly array $middlewares = [],
        public readonly string $moduleName = "",
        public readonly ?array $canaryInfo = null,
        public readonly ?array $flows = null,
    ) {
        //
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
        $pattern = $this->convertPathToRegex($this->path);
        
        if (!preg_match($pattern, $requestPath, $matches)) return false;

        array_shift($matches); // pop full match
            
        $this->mapParameters($matches);

        return true;
        
    }

    /**
     * Replace {param} with regex capture groups eg path "/products/{category}/{id}" produces something like \/products\/([^\/]+)\/([^\/]+) (characters are added and escaped for it to be a valid regex). when a live url eg "/products/shoes/42" is matched against this, those capture groups know what part of the string to extract
    */ 
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace(self::PLACEHOLDER_PATTERN, '([^/]+)', $path);
        
        // Escape forward slashes and add start/end anchors
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    private function mapParameters(array $matches): void
    {
        $paramNames = self::extractPlaceholders($this->path);
        
        foreach ($paramNames as $index => $paramName) {

            $paramValue = $matches[$index] ?? null;
            
            if ($paramValue !== null) {
                $this->parameters[$paramName] = $paramValue;
            }
        }
    }

    public static function extractPlaceholders(string $pattern): array
    {
        preg_match_all(self::PLACEHOLDER_PATTERN, $pattern, $matches);

        return $matches[1] ?? [];
    }

    public function handlerMatches(string $incomingHandler): bool {

        return $incomingHandler == $this->controllerMethod;
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