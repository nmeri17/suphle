<?php

namespace Suphle\Routing\Analysis;


use Suphle\Routing\AttributeRouteScanner;

use Suphle\Hydration\Container;

use Suphle\Contracts\Config\Router as RouterConfig;

use Suphle\Services\Decorators\BindsAsSingleton;
use ReflectionClass;

#[BindsAsSingleton]
class RouteListingService
{
    public function __construct(
        protected readonly AttributeRouteScanner $routeScanner,

        protected readonly PsalmSchemaAnalyzer $analyzerService
    ) {}
 
    public function getFormattedRows(?string $targetModule = null): array
    {
        $allRoutes = $this->routeScanner->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)
            ->getCoordinatorPath(),
            
            $this->analyzerService->analyzeCoordinator(...),

            $targetModule
        );
        $rows = [];

        foreach ($allRoutes as $route) {

            $rows[] = [
                $this->formatMethod($route['method']),
                $route['path'],
                $this->formatHandler($route),
                $this->formatResponse($route),
                $this->formatCollection($route['flows'] ?? [], 'type'),
                $this->formatValidators($route['validation_rules'] ?? []),
                $route['is_mirror'] ? '<fg=cyan>Yes</>' : '<fg=gray>No</>',
                $route["module_name"]
            ];
        }

        return $rows;
    }

    protected function formatMethod(string $method): string
    {
        $colors = ['GET' => 'green', 'POST' => 'yellow', 'PUT' => 'blue', 'DELETE' => 'red', 'PATCH' => 'cyan'];
        $color = $colors[strtoupper($method)] ?? 'white';
        return "<fg=$color>$method</>";
    }

    protected function formatHandler(array $route): string
    {
        $shortClass = (new ReflectionClass($route["coordinator"]))->getShortName();
        return "$shortClass@{$route['handler']}";
    }

    protected function formatResponse(array $route): string
    {
        $type = $route['response_shape']['type'] ?? 'mixed';
        $view = isset($route['view_name']) ? " ({$route['view_name']})" : "";
        return "<fg=magenta>$type</>$view";
    }

    protected function formatValidators(array $rules): string
    {
        if (empty($rules)) return '<fg=gray>-</>';
        return implode(", ", array_keys($rules));
    }

    protected function formatCollection(array $collection, string $key): string
    {
        if (empty($collection)) return '-';
        return implode(", ", array_map(function($item) use ($key) {
             return basename(str_replace('\\', '/', $item[$key]));
        }, $collection));
    }
}