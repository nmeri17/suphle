<?php
namespace Suphle\Routing;

use Suphle\Routing\Analysis\RouteAnalysisService;

use Suphle\Contracts\Config\Router as RouterConfig;

use Suphle\Hydration\Container;

use RuntimeException;

class NamedRouteReader
{
    public function __construct(
        protected readonly AttributeRouteScanner $routeScanner,
        private readonly RouteAnalysisService $analyzerService,
        private readonly PathPlaceholders $placeholders
    ) {}

    /**
     * Look up a named route and bind wildcard interpolations if passed
     */
    public function expandRoute(string $viewName, array $parameters = []): string
    {
        $allRoutes = $this->routeScanner->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->analyzerService->analyzeCoordinator(...)
        );

        foreach ($allRoutes as $route) {
            if ($route->viewName === $viewName) {
                return $this->interpolatePath($route->getFullPath(), $parameters);
            }
        }

        throw new RuntimeException(sprintf('Named route "%s" not found', $viewName));
    }

    private function interpolatePath(string $path, array $parameters): string
    {
        // Replace {param} placeholders with actual values from $parameters or PathPlaceholders
        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($parameters) {
            $paramName = $matches[1];
            
            if (array_key_exists($paramName, $parameters)) {
                return $parameters[$paramName];
            }

            $currentValue = $this->placeholders->getSegmentValue($paramName);
            if ($currentValue !== null) {
                return $currentValue;
            }

            throw new \InvalidArgumentException(sprintf('Missing required parameter "%s" for named route', $paramName));
        }, $path);
    }
}
