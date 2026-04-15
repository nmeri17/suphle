<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Hydration\Container;
use Suphle\Routing\{ModuleRequestRouter, RouteInfoExecutor};
use Suphle\Routing\Attributes\HttpMethod;
use Suphle\Request\RequestDetails;
use Suphle\Modules\Structures\ActiveDescriptors;
use Suphle\Contracts\{Modules\DescriptorInterface, Presentation\BaseRenderer};
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use ReflectionProperty;

class ModuleRequestRouterTest extends IsolatedComponentTest
{
    use RequestDetailsMocker;

    protected bool $usesRealDecorator = false;

    /**
     * Data provider for common route details structure returned by RAS
     */
    protected function getMockRouteList(): array
    {
        return [[
            "path" => "/api/v1/test",
            "method" => "GET",
            "coordinator" => "TestCoordinator",
            "handler" => "someMethod",
            "pre_middleware" => [],
            "middleware" => [],
            "module_name" => "ModuleOne",
            "view_name" => "test_view"
        ], [
            "path" => "/api/v1/users/{id}",
            "method" => "GET",
            "coordinator" => "UserCoordinator",
            "handler" => "showUser",
            "pre_middleware" => [],
            "middleware" => ["AuthMiddleware"],
            "module_name" => "ModuleOne",
            "view_name" => null
        ]];
    }

    public function test_canSetHandlingModule_returns_true_for_valid_route()
    {
        // Given
        $this->getRequestDetails("/api/v1/test"); // Trait handles container binding

        $router = $this->getContainer()->getClass(ModuleRequestRouter::class);

        // When
        $canHandle = $router->canSetHandlingModule($this->getMockRouteList());

        // Then
        $this->assertTrue($canHandle);
    }

    public function test_canSetHandlingModule_extracts_parameters_into_foundRoute()
    {
        // Given
        $this->getRequestDetails("/api/v1/users/123");

        $router = $this->getContainer()->getClass(ModuleRequestRouter::class);

        // When
        $router->canSetHandlingModule($this->getMockRouteList());
        
        // Verifying internal state extraction
        $property = new ReflectionProperty(ModuleRequestRouter::class, "foundRoute");
        $routeInfo = $property->getValue($router);

        // Then
        $this->assertNotNull($routeInfo);
        $this->assertEquals("123", $routeInfo->getAllParameters()["id"]);
    }

    public function test_triggerInfoModule_delegates_to_executor_in_descriptor_container()
    {
        // Given
        $routeList = $this->getMockRouteList();
        $this->getRequestDetails("/api/v1/test");

        $router = $this->getContainer()->getClass(ModuleRequestRouter::class);
        $router->canSetHandlingModule($routeList);

        // Doubles
        $mockRenderer = $this->positiveDouble(BaseRenderer::class);

        $executorDouble = $this->positiveDouble(RouteInfoExecutor::class, [
            "handleFoundRoute" => $mockRenderer
        ]);

        $moduleContainer = $this->positiveDouble(Container::class, [
            "getClass" => $executorDouble
        ]);

        $descriptorDouble = $this->positiveDouble(DescriptorInterface::class, [
            "getContainer" => $moduleContainer
        ]);

        $descriptorsList = $this->positiveDouble(ActiveDescriptors::class, [
            "findMatchingExports" => $descriptorDouble
        ]);

        // When
        $renderer = $router->triggerInfoModule($descriptorsList);

        // Then
        $this->assertSame($mockRenderer, $renderer);
        $this->assertSame($mockRenderer, $router->handlingRenderer());
        $this->assertSame($descriptorDouble, $router->getActiveModule());
    }

    public function test_canSetHandlingModule_returns_false_for_non_existent_route()
    {
        // Given
        $this->getRequestDetails("/non-existent");

        $router = $this->getContainer()->getClass(ModuleRequestRouter::class);

        // When
        $canHandle = $router->canSetHandlingModule($this->getMockRouteList());

        // Then
        $this->assertFalse($canHandle);
    }
}