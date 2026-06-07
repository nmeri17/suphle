<?php

namespace Suphle\Tests\Integration\Flows\Jobs\UpdateCountDelete;

use Suphle\Contracts\{Config\Router, Auth\AuthStorage};

use Suphle\Flows\{OuterFlowWrapper, Jobs\UpdateCountDelete};

use Suphle\Flows\Structures\{AccessContext, RouteUserNode, RouteUmbrella};

use Suphle\Hydration\Structures\ObjectDetails;

use Suphle\Response\Format\Json;

use Suphle\Services\BaseCoordinator;

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{ Routes\Flows\FlowRoutes, Meta\ModuleOneDescriptor, Config\RouterMock };

use DateTime;
use DateInterval;

class FlowRoutesUpdateCountTest extends JobFactory
{
    private string $resourceUrl = "/posts/5";
    private $aMinuteBehind;

    public function setUp(): void
    {

        parent::setUp();

        $this->aMinuteBehind = (new DateTime())->sub(new DateInterval("PT1M"));
    }

    protected function getModules(): array
    {

        return [

            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $container->replaceWithMock(Router::class, RouterMock::class, [

                    // "browserEntryRoute" => FlowRoutes::class // removed FlowRoutes
                ]);
            })
        ];
    }

    public function test_empties_cache_entry()
    {

        $this->handleUpdateCountDelete(); // given and when

        $this->assertNotHandledByFlow($this->resourceUrl); // then
    }

    private function handleUpdateCountDelete(): void
    {

        $this->makeUpdateCountDelete($this->makeAccessContext(
            $this->replaceConstructorArguments(RouteUserNode::class, $this->userNodeArguments())
        ))->handle(); // will push it into cache storage since hit is still =0

        $this->get($this->resourceUrl); // push delete task to queue

        $this->processQueuedTasks(); // execute delete task
    }

    private function userNodeArguments(): array
    {

        return [

            "renderer" => $this->replaceConstructorArguments(Json::class, [], [

                "getCoordinator" => $this->positiveDouble(BaseCoordinator::class)
            ])
        ];
    }

    private function makeAccessContext(RouteUserNode $unitPayload): AccessContext
    {

        $container = $this->getContainer();

        $objectMeta = $container->getClass(ObjectDetails::class);

        $routeUmbrella = new RouteUmbrella($this->resourceUrl, $objectMeta);

        $routeUmbrella->setAuthMechanism($container->getClass(AuthStorage::class)::class);

        return new AccessContext(
            $this->resourceUrl,
            $unitPayload,
            $routeUmbrella,
            OuterFlowWrapper::ALL_USERS
        );
    }

    private function makeUpdateCountDelete($dependency): UpdateCountDelete
    {

        $jobName = UpdateCountDelete::class;

        return $this->getContainer()->whenType($jobName)

        ->needsArguments([ $dependency::class => $dependency ])

        ->getClass($jobName);
    }

    public function test_empties_cache_entry_after_max_hits()
    {
        // 1. GIVEN: A cached flow set to 1 max hit
        $this->setupCachedResource(maxHits: 1);

        // 2. WHEN: We access the resource
        $this->get($this->resourceUrl); 
        $this->processQueuedTasks(); // Executes UpdateCountDelete

        // 3. THEN: The flow should no longer be handled (deleted)
        $this->assertNotHandledByFlow($this->resourceUrl);
    }

    public function test_retains_cache_if_hits_remaining()
    {
        // GIVEN: 2 hits allowed
        $this->setupCachedResource(maxHits: 2);

        // WHEN: Access once
        $this->get($this->resourceUrl);
        $this->processQueuedTasks();

        // THEN: Still exists
        $this->assertHandledByFlow($this->resourceUrl);
    }

    private function setupCachedResource(int $maxHits): void
    {
        $context = $this->makeAccessContext($maxHits);
        
        // Manually trigger the job that "warms" the cache
        $this->getContainer()->getClass(UpdateCountDelete::class)
            ->handle($context); 
    }

    public function test_wildcard_is_locked_to_mechanism()
    {
        $url = "/user-content/5";
        
        // 1. Warm cache for Session Auth
        $sessionContext = $this->makePendingFlowDetails($this->contentOwner, SessionStorage::class);
        $this->makeRouteBranches($sessionContext)->handle();

        // 2. Try to access via Token Auth (Mirroring scenario)
        $this->actingAs($this->contentOwner);
        $this->setAuthMechanism(TokenStorage::class);

        // 3. EXPECT: System should NOT find the session-based cache entry
        $this->assertNotHandledByFlow($url);
    }

    public function test_wont_empty_cache_entry()
    {

        $this->makeUpdateCountDelete($this->makeAccessContext(
            $this->replaceConstructorArguments(
                RouteUserNode::class,
                $this->userNodeArguments(),
                ["getMaxHits" => 2]
            ) // default [getExpiresAt] + this should retain the node
        ))->handle(); // given

        $this->assertHandledByFlow($this->resourceUrl); // when

        $this->assertHandledByFlow($this->resourceUrl); // then
    }

    public function test_expired_node_wont_be_handled_by_flow()
    {

        $this->dataProvider([

            $this->expiredContexts(...)
        ], function (RouteUserNode $payload) {

            $this->makeUpdateCountDelete($this->makeAccessContext($payload))->handle(); // given

            $this->assertNotHandledByFlow($this->resourceUrl); // then
        });
    }

    public function expiredContexts(): array
    {

        return [
            [
                $this->replaceConstructorArguments(
                    RouteUserNode::class,
                    $this->userNodeArguments(),
                    [

                    "getMaxHits" => 200,

                    "getExpiresAt" => $this->aMinuteBehind
                    ]
                )
            ],
            [
                $this->replaceConstructorArguments(RouteUserNode::class, $this->userNodeArguments(), [

                    "getExpiresAt" => $this->aMinuteBehind
                ])
            ]
        ];
    }
}
