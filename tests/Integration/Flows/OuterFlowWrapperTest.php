<?php
namespace Suphle\Tests\Integration\Flows;

use Suphle\Contracts\Config\Router as RouterContract;

use Suphle\Config\Router;

use Suphle\Testing\{Proxies\WriteOnlyContainer, Condiments\EmittedEventsCatcher};

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor};

class OuterFlowWrapperTest extends JobFactory
{
    use EmittedEventsCatcher;

    public function test_will_queueBranches_after_returning_flow_request()
    {
        // 1. GIVEN: Set the "Origin" to match FlowCoordinator::getCatalog
        $this->originDataName = "data"; // Matches the 'source' in your attribute
        $this->originMethod = "getCatalog"; // The method JobFactory will reflect on

        // 2. WHEN: Simulate the origin request finishing
        $this->handleDefaultPendingFlowDetails(); 

        // 3. THEN: Verify the queue received a task for ID 1 (first item in catalog)
        $this->assertPushedToFlow("posts/1"); 
    }
}
