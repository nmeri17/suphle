<?php
namespace Suphle\Tests\Unit\Routing;

use Suphle\Routing\Analysis\RendererContentShape;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\PrefixedCoordinator;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;
use ReflectionClass;

class RouteAnalysisServiceTest extends IsolatedComponentTest {
    use CommonBinds;

    public function test_analyzeCoordinator_resolves_prefixes_and_methods() {
        $analyzer = $this->getContainer()->getClass(RendererContentShape::class);
        
        // When
        $routes = $analyzer->analyzeCoordinator(PrefixedCoordinator::class, "ModuleOne");

        // Then
        $this->assertCount(1, $routes);
        $this->assertStringStartsWith("/admin", $routes[0]["path"]);
        $this->assertEquals("GET", strtoupper($routes[0]["method"]));
    }

    public function test_analyzeMethod_merges_middleware_correctly() {
        $analyzer = $this->getContainer()->getClass(RendererContentShape::class);
        $reflection = new ReflectionClass(PrefixedCoordinator::class);
        $method = $reflection->getMethod("someMethod");

        // Assuming analyzeMethod is protected, we test it via the public analyzeCoordinator
        $routes = $analyzer->analyzeCoordinator(PrefixedCoordinator::class, "ModuleOne");
        
        $this->assertIsArray($routes[0]["middleware"]);
        $this->assertIsArray($routes[0]["pre_middleware"]);
    }

    public function test_psalm_analyzer_detects_renderer_shapes() {
        $analyzer = $this->getContainer()->getClass(RendererContentShape::class);
        $reflection = new ReflectionClass(PrefixedCoordinator::class);
        $method = $reflection->getMethod("methodReturningJson");

        // When
        $shape = $analyzer->getResponseShape($method);

        // Then
        $this->assertEquals("object", $shape["type"]); // Or specific properties if Psalm is primed
    }
}