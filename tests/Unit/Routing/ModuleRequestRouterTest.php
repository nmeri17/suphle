<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Hydration\Container;
use Suphle\Routing\{ModuleRequestRouter, RouteInfoExecutor, Attributes\HttpMethod};

use Suphle\Request\RequestDetails;
use Suphle\Modules\Structures\ActiveDescriptors;
use Suphle\Contracts\{Modules\DescriptorInterface, Presentation\BaseRenderer};
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use ReflectionProperty;

class ModuleRequestRouterTest extends IsolatedComponentTest
{
    use RequestDetailsMocker;

    //protected bool $usesRealDecorator = false;

    /**
     * Data provider for common route details structure returned by RAS
     */
    protected function getMockRouteList(): array
    {
        return [[
            "path" => "/api/v1/test",
            "method" => HttpMethod::GET,
            "coordinator" => "BaseCoordinator",
            "handler" => "someMethod",
            "pre_middleware" => [],
            "middleware" => [],
            "module_name" => "ModuleOne",
        ], [
            "path" => "/api/v1/users/{id}",
            "method" => HttpMethod::GET,
            "coordinator" => "UserCoordinator",
            "handler" => "showUser",
            "pre_middleware" => [],
            "middleware" => ["AuthenticateHandler"], // should this be fqcn?
            "module_name" => "ModuleOne",
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

        $routeInfo = $router->getFoundRoute();

        // Then
        $this->assertNotNull($routeInfo);
        $this->assertEquals("123", $routeInfo->getAllSegmentValues()["id"]);
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