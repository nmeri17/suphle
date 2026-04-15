<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\{RouteInfoExecutor, PathPlaceholders, Structures\RouteInfo};

use Suphle\Middleware\MiddlewareQueue;

use Suphle\Testing\TestTypes\IsolatedComponentTest;

use Suphle\Tests\Integration\Generic\CommonBinds;

class RouteInfoExecutorTest extends IsolatedComponentTest {
    use CommonBinds;

    public function test_handleFoundRoute_orchestrates_full_lifecycle() {
        $container = $this->getContainer();

        // 1. Mock the MiddlewareQueue to return a dummy Renderer
        $mockRenderer = $this->positiveDouble(BaseRenderer::class, []);
        $queueMock = $this->positiveDouble(MiddlewareQueue::class, [
            "runStack" => $mockRenderer
        ]);

        // 2. Mock RendererManager to ensure it actually boots
        $rendererManagerMock = $this->positiveDouble(RendererManager::class, [
            "mayBeInvalid" => $this->returnSelf(),
            "bootDefaultRenderer" => null
        ], [
            "bootDefaultRenderer" => [1, []] // Verify it's called exactly once
        ]);

        // 3. Bind our mocks
        $this->massProvide([
            RendererManager::class => $rendererManagerMock,
            MiddlewareQueue::class => $queueMock
        ]);

        $executor = $container->getClass(RouteInfoExecutor::class);

        $route = new RouteInfo(
            path: "/users/{id}",
            method: "get",
            controllerClass: "SomeClass",
            controllerMethod: "someMethod",
            preMiddlewares: ["PreMidw"], // Test that these are passed
            middlewares: ["Midw"],
            parameters: ["id" => "42"]
        );

        // When
        $result = $executor->handleFoundRoute($route);

        // Then
        $this->assertSame($mockRenderer, $result); // Verify the conductor returns the queue result
        
        $placeholders = $container->getClass(PathPlaceholders::class);
        $this->assertEquals("42", $placeholders->getSegmentValue("id"));
    }
}