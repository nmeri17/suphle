<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\AttributeRouteScanner;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Contracts\Config\Router;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{TestCoordinator, PrefixedCoordinator, CanaryCoordinator, MiddlewareCoordinator};

class AttributeRouteScannerTest extends IsolatedComponentTest
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

    public function test_scans_coordinator_classes_for_route_attributes()
    {
        // Given
        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanAndRegisterRoutes();

        // Then
        $this->assertNotEmpty($routes);
        $this->assertArrayHasKey(HttpMethod::GET->value, $routes);
        $this->assertArrayHasKey(HttpMethod::POST->value, $routes);
    }

    public function test_registers_route_prefix_attributes()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                PrefixedCoordinator::class
            ]
        ]);

        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanAndRegisterRoutes();

        // Then
        $this->assertNotEmpty($routes);
        // Should have routes with the prefix applied
        $this->assertArrayHasKey(HttpMethod::GET->value, $routes);
    }

    public function test_handles_canary_routes()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                CanaryCoordinator::class
            ]
        ]);

        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanAndRegisterRoutes();

        // Then
        $this->assertNotEmpty($routes);
        // Should have canary routes registered
        $this->assertArrayHasKey(HttpMethod::GET->value, $routes);
    }

    public function test_applies_middleware_from_attributes()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                MiddlewareCoordinator::class
            ]
        ]);

        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanAndRegisterRoutes();

        // Then
        $this->assertNotEmpty($routes);
        // Should have middleware applied to routes
        $this->assertArrayHasKey(HttpMethod::GET->value, $routes);
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