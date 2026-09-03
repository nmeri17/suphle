<?php

namespace Suphle\Tests\Integration\Flows\Jobs\UpdateCountDelete;

use Suphle\Contracts\{Config\Router as RouterContract};

use Suphle\Config\Router;

use Suphle\Flows\{OuterFlowWrapper, UmbrellaSaver};

use Suphle\Flows\Structures\RouteUserNode;

use Suphle\Response\Format\Json;

use Suphle\Routing\{Structures\RouteInfo, Attributes\HttpMethod};

use Suphle\Auth\Storage\{SessionStorage, TokenStorage};

use Suphle\Testing\Proxies\WriteOnlyContainer;

use Suphle\Tests\Integration\Flows\Jobs\RouteBranches\JobFactory;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Coordinators\FlowCoordinator};

use DateTime, DateInterval;

class FlowRoutesUpdateCountTest extends JobFactory
{
    private string $resourceUrl = "/posts/5";
    private $aMinuteBehind;

    public function setUp(): void
    {
        parent::setUp();

        $this->aMinuteBehind = (new DateTime())->sub(new DateInterval("PT1M"));
    }

    public function test_empties_cache_entry()
    {
        $this->handleDefaultPendingFlowDetails(); // given

        $this->get($this->resourceUrl); // when
        $this->processQueuedTasks();

        $this->assertNotHandledByFlow($this->resourceUrl); // then
    }

    public function test_empties_cache_entry_after_max_hits()
    {
        $this->seedCache($this->buildUserNode(maxHits: 1)); // given

        $this->get($this->resourceUrl);
        $this->processQueuedTasks();

        $this->assertNotHandledByFlow($this->resourceUrl); // then
    }

    public function test_retains_cache_if_hits_remaining()
    {
        $this->seedCache($this->buildUserNode(maxHits: 2)); // given

        $this->get($this->resourceUrl);
        $this->processQueuedTasks();

        $this->assertHandledByFlow($this->resourceUrl); // then
    }

    public function test_wont_empty_cache_entry()
    {
        $this->seedCache($this->buildUserNode(maxHits: 3)); // given: survives two full access+accounting passes (hits>=maxHits-1: 0>=2 false, 1>=2 false)

        $this->get($this->resourceUrl);
        $this->processQueuedTasks(); // first pass

        $this->assertHandledByFlow($this->resourceUrl);

        $this->get($this->resourceUrl);
        $this->processQueuedTasks(); // second pass

        $this->assertHandledByFlow($this->resourceUrl); // then
    }

    public function test_expired_node_wont_be_handled_by_flow()
    {
        $this->dataProvider([

            $this->expiredContexts(...)
        ], function (RouteUserNode $node) {

            $this->seedCache($node); // given

            $this->assertNotHandledByFlow($this->resourceUrl); // then — notExpired() gate in getUserPayload() rejects it before any queueing happens
        });
    }

    protected function seedCache (RouteUserNode $nodeContent):void {

        $this->getContainer()->getClass(UmbrellaSaver::class)
        ->saveNewUmbrella(
            $this->resourceUrl, $nodeContent, $this->makePendingFlowDetails()
        );
    }

    public function expiredContexts(): array
    {
        return [
            [$this->buildUserNode(maxHits: 200, expiresAt: $this->aMinuteBehind)],
            [$this->buildUserNode(maxHits: 1, expiresAt: $this->aMinuteBehind)]
        ];
    }

    private function buildUserNode(int $maxHits, ?DateTime $expiresAt = null): RouteUserNode
    {
        $node = new RouteUserNode(
            new Json([]),
            new RouteInfo("posts/{id}", HttpMethod::GET, FlowCoordinator::class, "posts")
        );

        $node->setMetaDetails($expiresAt ?? $this->futureExpiry(), $maxHits);

        return $node;
    }

    private function futureExpiry(): DateTime
    {
        return (new DateTime())->add(new DateInterval("PT10M"));
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
}