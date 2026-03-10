<?php

namespace Suphle\Tests\Integration\Routing\Versioning;

use Suphle\Testing\TestTypes\ModuleLevelTest;
use Suphle\Testing\Proxies\WriteOnlyContainer;
use Suphle\Contracts\Config\Router;
use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;
use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;
use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{
    ProductsV1Coordinator,
    ProductsV2Coordinator
};

/**
 * Demonstrates the Suphle API versioning pattern:
 *
 * - A V2 coordinator can extend its V1 counterpart
 * - It defines a new #[RoutePrefix] for the v2 URL namespace
 * - PHP Reflection naturally exposes inherited public methods INCLUDING their #[Route] attributes
 * - The scanner registers all inherited routes under the child coordinator's prefix automatically
 * - Only the overridden method (store) differs between versions
 * - V1 routes remain completely untouched
 *
 * No route definitions need to be copy-pasted between versions.
 */
class CoordinatorVersioningTest extends ModuleLevelTest
{
    protected function getModules(): array
    {
        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {
                $container->replaceWithMock(Router::class, RouterMock::class, [
                    "getCoordinatorClassesToScan" => [
                        ProductsV1Coordinator::class,
                        ProductsV2Coordinator::class,
                    ]
                ]);
            })
        ];
    }

    // ---- V1 routes remain fully intact ----

    public function test_v1_index_responds_with_v1_payload()
    {
        $response = $this->get("/api/v1/products/");

        $this->assertEquals('v1', $response->getData()['version']);
    }

    public function test_v1_show_responds_with_v1_payload()
    {
        $response = $this->get("/api/v1/products/42");

        $this->assertEquals('v1', $response->getData()['version']);
    }

    public function test_v1_store_uses_legacy_schema()
    {
        $response = $this->post("/api/v1/products/", []);

        $data = $response->getData();
        $this->assertEquals('v1', $data['version']);
        $this->assertEquals('legacy', $data['schema']);
    }

    // ---- V2 inherits stable routes from V1 ----

    public function test_v2_index_is_inherited_from_v1_and_registered_under_v2_prefix()
    {
        $response = $this->get("/api/v2/products/");

        // Response comes from the INHERITED ProductsV1Coordinator::index(),
        // but routed through the v2 prefix because ProductsV2Coordinator defines
        // its own #[RoutePrefix("api/v2/products")].
        $this->assertEquals('v1', $response->getData()['version']);
    }

    public function test_v2_show_is_inherited_from_v1_and_registered_under_v2_prefix()
    {
        $response = $this->get("/api/v2/products/7");

        $this->assertEquals('v1', $response->getData()['version']);
    }

    // ---- V2 overridden method takes precedence ----

    public function test_v2_store_uses_new_v2_schema_not_the_v1_one()
    {
        $response = $this->post("/api/v2/products/", []);

        $data = $response->getData();
        $this->assertEquals('v2', $data['version']);
        $this->assertEquals('new', $data['schema']);
    }

    // ---- Scanner registers correct number of routes ----

    public function test_v2_coordinator_registers_all_three_routes_via_inheritance()
    {
        // V2 defines only store() explicitly but index() + show() are inherited.
        // After scanning, three distinct routes should be found under /api/v2/products.
        $routeManager = $this->getContainer()->getClass(
            \Suphle\Routing\AttributeRouteManager::class
        );

        $allRoutes = $routeManager->getAllRoutes();

        $v2Routes = array_filter($allRoutes, fn($r) => str_starts_with($r->getFullPath(), 'api/v2/products'));

        $this->assertCount(3, array_values($v2Routes),
            'V2 coordinator should have 3 routes: 2 inherited (index, show) + 1 overridden (store)'
        );
    }
}
