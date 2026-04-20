<?php

namespace Suphle\Tests\Integration\Flows\Jobs\RouteBranches;

use Suphle\Flows\{UmbrellaSaver, Structures\RouteUmbrella};

use Suphle\Flows\Structures\PendingFlowDetails;

use Suphle\Contracts\{IO\CacheManager, Config\Router, Presentation\BaseRenderer};

use Suphle\Testing\{Proxies\WriteOnlyContainer, Utilities\ArrayAssertions};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Flows\OriginCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

class IntraModuleTest extends JobFactory
{
    use ArrayAssertions;

    private string $user5Url = "/user-content/5";

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    "browserEntryRoute" => OriginCollection::class
                ]);
            })
        ];
    }

    public function test_stores_correct_data_in_cache()
    {
        $this->dataProvider([
            $this->contextParameters(...)
        ], function (PendingFlowDetails $context) {
            
            // This triggers the FlowHydrator via the RouteBranches job
            $this->makeRouteBranches($context)->handle(); 

            $flowSaver = $this->container->getClass(UmbrellaSaver::class);
            $location = $flowSaver->getPatternLocation($this->user5Url);
            $umbrella = $flowSaver->getExistingUmbrella($location);

            $this->assertNotNull($umbrella);

            // Verify that data for ID 5 was actually rendered and cached
            $cachedResponse = $this->extractResponse($umbrella, $context->getStoredUserId());
            $this->assertEquals(5, $cachedResponse['id']); 
        });
    }

    public function test_no_flow_attribute_does_nothing()
    {
        // Ensure paths without the #[Flow] attribute don't trigger queue activity
        $this->assertNotPushedToFlow("/no-flow-attribute");
    }

    /**
     * The test this goes into doesn't do any auth related stuff. It is content with running the flow and expecting to find it in the cache
     *
     * @return [
         * 	PendingFlowDetails => configured to match what we expect an origin url to populate a task with
     * ]
    */
    public function contextParameters(): array
    {

        return [
            [$this->makePendingFlowDetails()],

            [
                $this->makePendingFlowDetails($this->contentOwner),

                $this->contentOwner
            ]
        ];
    }

    private function extractResponse(RouteUmbrella $routeUmbrella, string $userId): array
    {

        return $routeUmbrella->getUserPayload($userId)

        ->getRenderer()->getRawResponse();
    }

    public function test_will_be_handled_by_flow()
    {

        $this->dataProvider([

            $this->contextParameters(...)
        ], function (PendingFlowDetails $context) {

            // given => see dataProvider
            $this->makeRouteBranches($context)->handle(); // When

            // then
            $this->assertHandledByFlow($this->user5Url);
        });
    }

    public function test_no_flow_does_nothing()
    {

        $this->assertNotPushedToFlow("/no-flow");
    }
}
