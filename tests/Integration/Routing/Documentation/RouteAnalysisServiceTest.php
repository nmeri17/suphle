<?php
namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Routing\Analysis\RendererContentShape;

use Suphle\Hydration\Container;

use Suphle\Testing\TestTypes\ModuleLevelTest;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\PrefixedCoordinator};
use ReflectionClass;

class RouteAnalysisServiceTest extends ModuleLevelTest {

    protected function getModules(): array
    {
        return [new ModuleOneDescriptor(new Container)];
    }

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

        $routes = $analyzer->analyzeCoordinator(PrefixedCoordinator::class, "ModuleOne");
        
        $this->assertIsArray($routes[0]["middleware"]);
        $this->assertIsArray($routes[0]["pre_middleware"]);
    }

    public function test_static_analyzer_detects_renderer_shapes() {
        
        $analyzer = $this->getContainer()->getClass(RendererContentShape::class);
        $reflection = new ReflectionClass(PrefixedCoordinator::class);
        $method = $reflection->getMethod("methodReturningJson");

        // When
        $shape = $analyzer->getResponseShape($method);

        // Then
        $this->assertEquals("object", $shape["type"]);
    }
}