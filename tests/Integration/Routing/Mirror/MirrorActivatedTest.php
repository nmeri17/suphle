<?php
namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Tests\Integration\Routing\TestsRouter;
use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\{ProductsV1Coordinator, BaseCoordinator};

class MirrorActivatedTest extends TestsRouter
{
    protected array $coordinators = [
        BaseCoordinator::class, ProductsV1Coordinator::class
    ];

    public function test_can_switch_to_api_collection()
    {
        $matchingRouteInfo = $this->findRouteInfo("/api/v1/products"); // when

        $this->assertNotNull($matchingRouteInfo);

        $this->assertTrue($matchingRouteInfo->handlerMatches("index")); // then
    }

    public function test_only_enabled_works()
    {

        $matchingRouteInfo = $this->findRouteInfo("/api/v1/segment"); // when

        $this->assertNull($matchingRouteInfo); // then
    }
}
