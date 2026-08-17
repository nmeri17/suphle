<?php
namespace Suphle\Tests\Integration\Auth;

use Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators\SecureCoordinatorV2;

class BrowserVersioningTest extends BaseAuthTest {

    protected string $coordinatorName = SecureCoordinatorV2::class;

    public function test_overridden_pattern_requires_auth()
    {

        // given no given user

        $responseAsserter = $this->get("/secure/data"); // when

        $responseAsserter->assertUnauthorized(); // then
    }

    public function test_session_impersonate()
    {

        [$user1, $user2] = $this->replicator->getRandomEntities(2);

        $this->actingAs($user1); // given

        $sut = $this->getAuthStorage();

        $sut->imitate($user2->getId()); // when

        $this->assertAuthenticatedAs($user2); // then

        $this->assertTrue($sut->getPreviousUser() == $user1->getId()); // int/string comparison
    }

    public function test_nested_route_can_unlink_auth()
    {
        $this->get("/secure/unlink") // when

        ->assertOk(); // then
    }
}
