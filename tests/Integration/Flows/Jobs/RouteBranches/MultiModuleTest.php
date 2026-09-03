<?php

namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

use Suphle\Hydration\Container;

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Modules\ModuleThree\Meta\ModuleThreeDescriptor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

class MultiModuleTest extends JobFactory
{

    protected function getModules ():array {

        $moduleOne = $this->createFlowModule(ModuleOneDescriptor::class, [$this->rendererController]);

        $moduleThree = (new ModuleThreeDescriptor(new Container()))

        ->sendExpatriates([

            ModuleOne::class => $moduleOne
        ]);

        return [
            $moduleOne, $moduleThree
        ];
    }

    public function test_handle_flows_in_other_modules()
    {

        $this->get("/flow-to-module3"); // given

        $this->processQueuedTasks(); // when

        $this->assertHandledByFlow("/module-three/5"); // then
    }
}
