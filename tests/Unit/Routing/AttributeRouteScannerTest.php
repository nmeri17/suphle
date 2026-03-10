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
        $routes = $scanner->scanClass(TestCoordinator::class);

        // Then
        $this->assertNotEmpty($routes);
        $this->assertEquals(HttpMethod::GET->value, $routes[0]->method);
        $this->assertEquals(HttpMethod::POST->value, $routes[1]->method);
    }

    public function test_registers_route_prefix_attributes()
    {
        // Given
        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanClass(PrefixedCoordinator::class);

        // Then
        $this->assertNotEmpty($routes);
        // Should have routes with the prefix applied
        $this->assertStringContainsString("admin", $routes[0]->path);
    }

    public function test_handles_canary_routes()
    {
        // Given
        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanClass(CanaryCoordinator::class);

        // Then
        $this->assertNotEmpty($routes);
        // Should have canary routes registered
        $this->assertNotNull($routes[0]->canaryInfo);
    }

    public function test_applies_middleware_from_attributes()
    {
        // Given
        $scanner = $this->container->getClass(AttributeRouteScanner::class);

        // When
        $routes = $scanner->scanClass(MiddlewareCoordinator::class);

        // Then
        $this->assertNotEmpty($routes);
        // Should have middleware applied to routes
        $this->assertNotEmpty($routes[0]->middlewares);
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