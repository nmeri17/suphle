<?php
namespace Suphle\WebSockets;

use Suphle\Routing\AttributeRouteScanner;
use Suphle\Hydration\{Container, Structures\ObjectDetails};
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\WebSockets\Attributes\WsRoute;
use ReflectionClass;

class WebSocketRouter
{
    private array $routes = [];

    public function __construct(
        protected readonly AttributeRouteScanner $scanner,

        protected readonly ObjectDetails $objectDetails
    ) {}

    public function registerRoutes(): void
    {
        $this->routes = $this->scanner->scanModulesByPath(
            fn (Container $container) => $container->getClass(RouterConfig::class)->getWebSocketPath(),
            
            $this->extractWsAttributes(...)
        );
    }

    private function extractWsAttributes(string $className, string $moduleName): array
    {
        $found = [];

        $attributes = $this->objectDetails->getClassAttributes($className, WsRoute::class);
        
        foreach ($attributes as $attr) {
            // We return a keyed array so the Scanner can merge them
            $found[$attr->newInstance()->path] = $className;
        }
        return $found;
    }
    public function getHandlerFor(string $path): ?string
    {
        return $this->routes[$path] ?? null;
    }
}