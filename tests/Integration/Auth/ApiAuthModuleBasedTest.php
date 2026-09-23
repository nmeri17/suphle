<?php
namespace Suphle\Tests\Integration\Auth;

use Suphle\Auth\Storage\{SessionStorage};

use Suphle\Exception\Explosives\UnexpectedAuthentication;

class ApiAuthModuleBasedTest extends AuthTestBase {

    public function test_cant_access_api_auth_route_with_session()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, SessionStorage::class); // given

        $this->get($this->expandUrl("dashboard", $this->coordinatorName, true)) // when

        ->assertUnauthorized(); // then
    }

    public function test_cant_access_guest_midd_route_when_authed()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user); // given

        $url = $this->expandUrl("guestEndpoint");

        $responseAsserter = $this->get($url); // when

        $responseAsserter->assertUnauthorized(); // then

        $responseAsserter->assertJson([
            'error' => UnexpectedAuthentication::class
        ]);
    }
}
