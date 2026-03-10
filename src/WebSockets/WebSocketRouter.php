<?php

namespace Suphle\WebSockets;

use Suphle\Hydration\Container;
use Suphle\Modules\Structures\ActiveDescriptors;
use Suphle\WebSockets\Attributes\WsRoute;
use ReflectionClass;

class WebSocketRouter
{
    private array $routes = [];

    public function __construct(
        private readonly Container $container
    ) {}

    public function registerRoutes(): void
    {
        $activeDescriptors = $this->container->getClass(ActiveDescriptors::class);
        $modules = $activeDescriptors->getOriginalDescriptors();
        
        foreach ($modules as $module) {
            $this->scanModuleForGateways($module->getContainer());
        }
    }

    private function scanModuleForGateways(Container $moduleContainer): void
    {
        $moduleFiles = $moduleContainer->getClass(\Suphle\Contracts\Config\ModuleFiles::class);
        $moduleRoot = $moduleFiles->activeModulePath();
        
        // Scan the WebSockets or Gateways directory if they exist
        $directoriesToScan = [
            $moduleRoot . 'WebSockets',
            $moduleRoot . 'Gateways',
            $moduleRoot . 'Controllers/WebSockets' // common alternative
        ];

        foreach ($directoriesToScan as $directory) {
            if (is_dir($directory)) {
                $this->scanDirectory($directory);
            }
        }
    }

    private function scanDirectory(string $directory): void
    {
        $files = glob($directory . '/*.php');
        
        foreach ($files as $file) {
            $className = $this->getClassNameFromFile($file);
            if ($className) {
                $this->scanClass($className);
            }
        }
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

    private function scanClass(string $className): void
    {
        if (!class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);
        $routeAttributes = $reflection->getAttributes(WsRoute::class);
        
        if (!empty($routeAttributes)) {
            $routeAttribute = $routeAttributes[0]->newInstance();
            // Bind the gateway class to the path
            $this->addRoute($routeAttribute->path, $className);
        }
    }

    public function getHandlerFor(string $path): ?WebSocketGateway
    {
        return isset($this->routes[$path]) ? $this->container->getClass($this->routes[$path]) : null;
    }

    public function addRoute(string $path, string $handlerClass): void
    {
        $this->routes[$path] = $handlerClass;
    }
}
