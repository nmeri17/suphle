<?php

namespace Suphle\Tests\Integration\Events;

use Suphle\Contracts\Events;

use Suphle\Tests\Integration\Modules\ModuleDescriptor\DescriptorCollection;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Events\LocalReceiver, Meta\ModuleApi, Concretes\LocalSender};

use Suphle\Tests\Mocks\Interactions\{ModuleOne, ModuleThree};

class EventSubscriptionTest extends DescriptorCollection
{
    protected const EVENT_PAYLOAD = false;

    protected $container;

    protected Events $eventManager;

    protected function setUp(): void
    {

        parent::setUp();

        $this->container = $this->getContainer();

        $this->eventManager = $this->container->getClass(Events::class);
    }

    public function test_local_subscription_doesnt_see_external_subscription()
    {

        // given @see moduleOne event binding

        $this->container->getClass(LocalReceiver::class)

        ->reboundExternalEvent(self::EVENT_PAYLOAD); // when

        $localSubscription = $this->eventManager->getLocalHandler(LocalReceiver::class);

        $this->assertEmpty($localSubscription->getMatchingUnits(
            ModuleOne::OUTSIDERS_REBOUND_EVENT
        )); // then

        $foreignSubscription = $this->eventManager->getExternalHandlers(LocalReceiver::class);

        $this->assertNull($foreignSubscription);
    }

    /**
     * @depends test_local_subscription_doesnt_see_external_subscription
    */
    public function test_external_rebound_is_invisible_to_local_subscription()
    {

        // given @see moduleOne event binding

        $this->container->getClass(LocalSender::class)

        ->beginExternalCascade(self::EVENT_PAYLOAD); // when

        $subscription = $this->eventManager->getLocalHandler(LocalReceiver::class);

        $this->assertEmpty($subscription->getMatchingUnits(
            ModuleOne::OUTSIDERS_REBOUND_EVENT
        )); // then

        $foreignSubscription = $this->eventManager->getExternalHandlers(LocalReceiver::class);

        $this->assertNull($foreignSubscription);
    }

    /**
     * @depends test_external_rebound_is_invisible_to_local_subscription
    */
    public function test_external_rebound_is_visible_to_external_subscription()
    {

        // given @see moduleOne event binding

        $this->container->getClass(LocalSender::class)

        ->beginExternalCascade(self::EVENT_PAYLOAD); // when

        $eventManager = $this->getContainerFor(ModuleThree::class)

        ->getClass(Events::class);

        $subscription = $eventManager->getExternalHandlers(ModuleOne::class);

        $matchingHandlers = $subscription->getMatchingUnits(
            ModuleOne::OUTSIDERS_REBOUND_EVENT
        );

        $this->assertNotEmpty($matchingHandlers); // then

        $this->assertCount(1, $matchingHandlers);

        $localSubscription = $eventManager->getLocalHandler(ModuleOne::class);

        $this->assertEmpty($localSubscription->getMatchingUnits(
            ModuleOne::OUTSIDERS_REBOUND_EVENT
        )); // there's a legitimately invalid listener to this module, so it won't return null. But this event is not part of the events listened to
    }
}
