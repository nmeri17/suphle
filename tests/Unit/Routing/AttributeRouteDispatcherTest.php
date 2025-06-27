<?php

namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\AttributeRouteDispatcher;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Contracts\Config\Router;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{TestCoordinator, MiddlewareCoordinator, CanaryCoordinator};

class AttributeRouteDispatcherTest extends IsolatedComponentTest
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

    public function test_dispatches_route_to_coordinator_method()
    {
        // Given
        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);
        $dispatcher->scanAndRegisterRoutes();

        // When
        $result = $dispatcher->dispatchRoute("/api/v1/test", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('users', $result);
    }

    public function test_handles_middleware_execution()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                MiddlewareCoordinator::class
            ]
        ]);

        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);
        $dispatcher->scanAndRegisterRoutes();

        // When
        $result = $dispatcher->dispatchRoute("/api/v1/secure", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($result);
        // Middleware should have been executed
    }

    public function test_handles_canary_route_evaluation()
    {
        // Given
        $this->stubConfig([
            "getCoordinatorClassesToScan" => [
                CanaryCoordinator::class
            ]
        ]);

        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);
        $dispatcher->scanAndRegisterRoutes();

        // When
        $result = $dispatcher->dispatchRoute("/api/v1/beta", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($result);
        // Canary evaluation should have been performed
    }

    public function test_returns_null_for_non_existent_route()
    {
        // Given
        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);

        // When
        $response = $dispatcher->dispatch("/non-existent", HttpMethod::GET->value);

        // Then
        $this->assertNull($response);
    }

    public function test_handles_route_with_parameters()
    {
        // Given
        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);

        // When
        $response = $dispatcher->dispatch("/api/v1/users/123", HttpMethod::GET->value);

        // Then
        $this->assertNotNull($response);
    }

    public function test_handles_different_http_methods()
    {
        // Given
        $dispatcher = $this->container->getClass(AttributeRouteDispatcher::class);

        // When & Then
        $getResponse = $dispatcher->dispatch("/api/v1/users", HttpMethod::GET->value);
        $this->assertNotNull($getResponse);

        $postResponse = $dispatcher->dispatch("/api/v1/users", HttpMethod::POST->value);
        $this->assertNotNull($postResponse);

        $putResponse = $dispatcher->dispatch("/api/v1/users/123", HttpMethod::PUT->value);
        $this->assertNotNull($putResponse);

        $deleteResponse = $dispatcher->dispatch("/api/v1/users/123", HttpMethod::DELETE->value);
        $this->assertNotNull($deleteResponse);
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