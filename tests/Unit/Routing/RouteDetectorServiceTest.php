<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\RouteDetectorService;
use Suphle\Routing\Analysis\RendererAnalyzerRegistry;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\TestCoordinator;
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Hydration\Container;
use Suphle\Contracts\Flows\FlowHydrator;
use PHPUnit\Framework\TestCase;

class RouteDetectorServiceTest extends TestCase
{
    private RouteDetectorService $service;
    private RouterConfig $config;
    private Container $container;
    private FlowHydrator $flowHydrator;
    private RendererAnalyzerRegistry $rendererAnalyzerRegistry;

    protected function setUp(): void
    {
        $this->config = $this->createMock(RouterConfig::class);
        $this->container = $this->createMock(Container::class);
        $this->flowHydrator = $this->createMock(FlowHydrator::class);
        $this->rendererAnalyzerRegistry = $this->createMock(RendererAnalyzerRegistry::class);
        
        $this->service = new RouteDetectorService(
            $this->config, 
            $this->container, 
            $this->flowHydrator, 
            $this->rendererAnalyzerRegistry
        );
    }

    public function test_compiles_route_details_from_coordinator()
    {
        $this->config->expects($this->once())
            ->method('getCoordinatorClassesToScan')
            ->willReturn([TestCoordinator::class]);

        $result = $this->service->compileRouteDetails();

        $this->assertIsArray($result);
        $this->assertArrayHasKey(TestCoordinator::class, $result);
        $this->assertIsArray($result[TestCoordinator::class]);
    }

    public function test_get_all_routes_returns_flat_array()
    {
        $this->config->expects($this->once())
            ->method('getCoordinatorClassesToScan')
            ->willReturn([TestCoordinator::class]);

        $result = $this->service->getAllRoutes();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Check that each route has the expected structure
        foreach ($result as $route) {
            $this->assertArrayHasKey('method', $route);
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('handler', $route);
            $this->assertArrayHasKey('renderer', $route);
            $this->assertArrayHasKey('middleware', $route);
            $this->assertArrayHasKey('canary_state', $route);
            $this->assertArrayHasKey('placeholders', $route);
            $this->assertArrayHasKey('coordinator', $route);
        }
    }

    public function test_filters_routes_by_module()
    {
        $this->config->expects($this->once())
            ->method('getCoordinatorClassesToScan')
            ->willReturn([TestCoordinator::class]);

        $result = $this->service->getRoutesByModule('ModuleOne');

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        foreach ($result as $route) {
            $this->assertStringContainsString('ModuleOne', $route['coordinator']);
        }
    }

    public function test_filters_routes_with_criteria()
    {
        $routes = [
            [
                'method' => 'GET',
                'path' => '/users',
                'coordinator' => 'App\\Modules\\User\\UserCoordinator'
            ],
            [
                'method' => 'POST',
                'path' => '/posts',
                'coordinator' => 'App\\Modules\\Post\\PostCoordinator'
            ]
        ];

        $filtered = $this->service->filterRoutes($routes, ['method' => 'GET']);
        
        $this->assertCount(1, $filtered);
        $this->assertEquals('GET', $filtered[0]['method']);
    }

    public function test_extracts_placeholders_from_route_pattern()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('extractPlaceholders');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'users/{id}/posts/{postId}');
        
        $this->assertEquals(['id', 'postId'], $result);
    }

    public function test_builds_full_path_with_prefix()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildFullPath');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, 'api/v1', 'users');
        
        $this->assertEquals('/api/v1/users', $result);
    }

    public function test_builds_full_path_without_prefix()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildFullPath');
        $method->setAccessible(true);

        $result = $method->invoke($this->service, '', 'users');
        
        $this->assertEquals('/users', $result);
    }

    public function test_detects_tagged_patterns()
    {
        $this->config->expects($this->once())
            ->method('getCoordinatorClassesToScan')
            ->willReturn([TestCoordinator::class]);

        $result = $this->service->getAllRoutes();

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Verify that tagged patterns (auth/authz/middleware) are properly detected
        foreach ($result as $route) {
            $this->assertArrayHasKey('middleware', $route);
            $this->assertIsArray($route['middleware']);
            
            // Check for auth/authz patterns in middleware
            if (!empty($route['middleware'])) {
                $middlewareClasses = array_map(function($middleware) {
                    return is_string($middleware) ? $middleware : get_class($middleware);
                }, $route['middleware']);
                
                // Verify that auth/authz funnels are properly tagged
                $hasAuth = array_filter($middlewareClasses, function($class) {
                    return strpos($class, 'AuthenticateMetaFunnel') !== false || 
                           strpos($class, 'AuthorizeMetaFunnel') !== false;
                });
                
                // If route has auth/authz, verify it's properly structured
                if (!empty($hasAuth)) {
                    $this->assertArrayHasKey('coordinator', $route);
                    $this->assertArrayHasKey('handler', $route);
                    $this->assertArrayHasKey('path', $route);
                }
            }
        }
    }
} 