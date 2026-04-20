<?php

namespace Suphle\Tests\Integration\Flows;

use Suphle\Flows\OuterFlowWrapper;

use Suphle\Contracts\Config\Router;

use Suphle\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Coordinators\FlowCoordinator, Meta\ModuleOneDescriptor, Config\RouterMock};

class OuterFlowWrapperTest extends JobFactory
{
    use EmittedEventsCatcher;

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "getCoordinatorClassesToScan" => [FlowCoordinator::class]
                ]);
            })
        ];
    }

    public function test_will_queueBranches_after_returning_flow_request()
    {
        // 1. GIVEN: Set the "Origin" to match FlowCoordinator::getCatalog
        $this->originDataName = "data"; // Matches the 'source' in your attribute
        $this->originMethod = "getCatalog"; // The method JobFactory will reflect on

        // 2. WHEN: Simulate the origin request finishing
        $this->handleDefaultPendingFlowDetails(); 

        // 3. THEN: Verify the queue received a task for ID 1 (first item in catalog)
        $this->assertPushedToFlow("books/1"); 
    }
}
