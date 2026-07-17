<?php

namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor};

use Suphle\Tests\Integration\Routing\TestsRouter;

class MirrorDeactivatedTest extends TestsRouter
{
    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(RouterContract::class, Router::class, [

                    "getCoordinatorClassesToScan" => [$this->getEntryCollection()],

                    "mirrorsCollections" => false // this is now on a coordinator basis. so link one where a route was disabled
                ]);
            })
        ];
    }

    public function test_disable_mirror_blocks_those_routes()
    {

        // given @see mock

        $matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

        $this->assertNull($matchingRenderer); // then
    }
}
