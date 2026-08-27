<?php
namespace Suphle\Tests\Integration\Routing\Documentation;

use Suphle\Routing\Analysis\RendererContentShape;

use Suphle\Hydration\Container;

use Suphle\Routing\Attributes\HttpMethod;

use Suphle\Auth\Middleware\{AuthenticateHandler, MustBeGuestHandler};

use Suphle\Testing\TestTypes\ModuleLevelTest;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\SecureCoordinator};

class RouteAnalysisServiceTest extends ModuleLevelTest {

    protected function getModules(): array
    {
        return [new ModuleOneDescriptor(new Container)];
    }

    public function test_analyzeCoordinator_resolves_prefixes_and_methods() {

        $target = $this->routesByCoordinator("/secure/dashboard");

        // Then
        $this->assertNotEmpty($target);
        
        $this->assertSame(HttpMethod::GET, $target["method"]);
    }

    protected function routesByCoordinator (?string $targetPath = null, string $coordinatorName = SecureCoordinator::class):array {

        $routes = $this->getContainer()->getClass(RendererContentShape::class)

        ->analyzeCoordinator(// When
            $coordinatorName, $this->modules[0]->exportsImplements()
        );
        if (is_null($targetPath)) return $routes;

        return array_filter(
            $routes, fn (array $entry) => $entry["path"] == $targetPath
        );
    }

    public function test_analyzeMethod_merges_middleware_correctly() {

        $target = $this->routesByCoordinator("/secure/dashboard");

        // Then
        $this->assertSame([AuthenticateHandler::class], $target["pre_middleware"]);
    }

    public function test_static_analyzer_detects_renderer_shapes() {

        $shape = $this->routesByCoordinator("/secure/data")["response_shape"];
var_dump($shape["properties"]); // assert this also when we see what its contents are
        // Then
        $this->assertSame("object", $shape["type"]);
    }
}