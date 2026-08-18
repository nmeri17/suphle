<?php
namespace Suphle\Routing;

use Suphle\Routing\{Structures\RouteInfo};

use RuntimeException, InvalidArgumentException;

class NamedRouteReader
{
    public function __construct(
        protected readonly AttributeRouteScanner $routeScanner,
        protected readonly RouteInfo $placeholders
    ) {}

    /**
     * Look up a named route and bind wildcard interpolations if passed. when segment is missing in array, it will attempt reading that value from ri
     * 
     * @param {useMirror} when true, returns the api variant (if the route supports it)
     */
    public function expressUrl(string $coordinatorClass, string $methodName, array $parameters = [], bool $useMirror = false): string
    {

        foreach ($this->routeScanner->scanAllModules() as $route) {
            if (
                $route["coordinator"] === $coordinatorClass &&
                $route["handler"] === $methodName &&
                ($route["is_mirror"] ?? false) === $useMirror
            ) {
                return $this->interpolatePath($route["path"], $parameters);
            }
        }
        throw new RuntimeException(sprintf('Named route "%s::%s" not found', $coordinatorClass, $methodName));
    }

    protected function interpolatePath(string $path, array $parameters): string
    {
        // Replace {param} placeholders with actual values from $parameters or RouteInfo
        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($parameters) {
            $paramName = $matches[1];
            
            if (array_key_exists($paramName, $parameters)) {
                return $parameters[$paramName];
            }

            $currentValue = $this->placeholders->getSegmentValue($paramName);
            if ($currentValue !== null) {
                return $currentValue;
            }

            throw new InvalidArgumentException(sprintf('Missing required parameter "%s" for named route', $paramName));
        }, $path);
    }
}
