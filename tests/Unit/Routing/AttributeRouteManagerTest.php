<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\AttributeRouteManager;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Contracts\Config\Router;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{TestCoordinator, PrefixedCoordinator, CanaryCoordinator, MiddlewareCoordinator};

class AttributeRouteManagerTest extends IsolatedComponentTest
{
    use CommonBinds;

    protected bool $usesRealDecorator = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stubConfig([
            "getCoordinatorPath" => "Coordinators",
            "getCoordinatorClassesToScan" => [
                TestCoordinator::class
            ]
        ]);
    }

    public function test_registers_routes_from_coordinator_classes()
    {
        // Given
        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        $manager->scanAndRegisterRoutes();

        // Then
        $this->assertTrue($manager->hasRoutes());
    }

    public function test_finds_matching_route_for_request()
    {
        // Given
        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        $route = $manager->findRoute("/api/v1/test/", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($route);
        $this->assertEquals(HttpMethod::GET->value, $route->method);
        $this->assertEquals("/api/v1/test/", $route->path);
    }

    public function test_returns_null_for_non_matching_route()
    {
        // Given
        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        $route = $manager->findRoute("/non-existent", HttpMethod::GET->value);

        // Then
        $this->assertNull($route);
    }

    public function test_handles_route_with_middleware()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                MiddlewareCoordinator::class
            ],
            "getCoordinatorPath" => "Coordinators"
        ]);

        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        // Assuming MiddlewareCoordinator has a route at /api/v1/secure
        $route = $manager->findRoute("/api/v1/secure/", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($route);
        $this->assertNotEmpty($route->middlewares);
    }

    public function test_handles_canary_routes()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                CanaryCoordinator::class
            ],
            "getCoordinatorPath" => "Coordinators"
        ]);

        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        $route = $manager->findRoute("/api/v1/beta/", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($route);
        $this->assertNotNull($route->canaryInfo);
    }

    public function test_handles_route_prefixes()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                PrefixedCoordinator::class
            ],
            "getCoordinatorPath" => "Coordinators"
        ]);

        $manager = $this->container->getClass(AttributeRouteManager::class);

        // When
        $route = $manager->findRoute("/api/v1/admin/users/", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($route);
        $this->assertStringContainsString("admin", $route->path);
    }

    private function stubConfig(array $stubMethods): void
    {
        $this->massProvide([
            Router::class => $this->positiveDouble(
                RouterMock::class,
                $stubMethods
            )
        ]);
    }
} 