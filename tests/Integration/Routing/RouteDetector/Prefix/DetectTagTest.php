<?php
namespace Suphle\Tests\Integration\Routing\RouteDetector\Prefix;

use Suphle\Contracts\Config\Router;
use Suphle\Routing\RouteDetectorService;
use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};
use Suphle\Tests\Integration\Routing\RouteDetector\RouteDetectorAsserter;
use Suphle\Tests\Mocks\Modules\ModuleOne\{Config\RouterMock, Coordinators\TestCoordinator, Meta\ModuleOneDescriptor};

class DetectTagTest extends ModuleLevelTest {
    
    use RouteDetectorAsserter;

    protected function getModules(): array {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [TestCoordinator::class]
                ]);
            })
        ];
    }

    public function test_detects_routes_from_coordinator() {
        $service = $this->getService();
        $routeDetails = $service->compileRouteDetails();

        $this->assertIsArray($routeDetails);
        $this->assertArrayHasKey(TestCoordinator::class, $routeDetails);
        $this->assertIsArray($routeDetails[TestCoordinator::class]);
    }

    public function test_extracts_route_information() {
        $service = $this->getService();
        $allRoutes = $service->getAllRoutes();

        $this->assertIsArray($allRoutes);
        $this->assertNotEmpty($allRoutes);

        foreach ($allRoutes as $route) {
            $this->assertArrayHasKey('method', $route);
            $this->assertArrayHasKey('path', $route);
            $this->assertArrayHasKey('handler', $route);
            $this->assertArrayHasKey('renderer', $route);
            $this->assertArrayHasKey('middleware', $route);
            $this->assertArrayHasKey('canary_state', $route);
            $this->assertArrayHasKey('placeholders', $route);
            $this->assertArrayHasKey('coordinator', $route);
        }
    }

    protected function getService(): RouteDetectorService {
        return $this->getContainer()->getClass(RouteDetectorService::class);
    }
}