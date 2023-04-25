<?php

namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\FlowRoutes, Config\RouterMock};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

class MultiModuleTest extends JobFactory
{
    protected function getModules(): array
    {

        return [

            $this->moduleOne, $this->moduleThree
        ];
    }

    protected function setModuleOne(): void
    {

        $this->moduleOne = $this->replicateModule(
            ModuleOneDescriptor::class,
            function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => FlowRoutes::class
                ]);
            }
        );
    }

    public function test_handle_flows_in_other_modules()
    {

        $this->get("/flow-to-module3"); // given

        $this->processQueuedTasks(); // when

        $this->assertHandledByFlow("/module-three/5"); // then
    }
}
