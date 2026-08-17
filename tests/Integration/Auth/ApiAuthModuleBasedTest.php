<?php
namespace Suphle\Tests\Integration\Auth;

use Suphle\Auth\Storage\{SessionStorage, TokenStorage};

use Suphle\Exception\Explosives\UnexpectedAuthentication;

class ApiAuthModuleBasedTest extends BaseAuthTest {

    public function test_cant_access_api_auth_route_with_session()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, SessionStorage::class); // given

        $this->get("/dashboard") // when

        ->assertUnauthorized(); // then
    }

    public function test_cant_access_guest_midd_route_when_authed()
    {

        $user = $this->replicator->getRandomEntity();

        $token = $this->actingAs($user, TokenStorage::class); // given

        $responseAsserter = $this->get("/strictly-guest", [], [

            "Authorization" => "Bearer $token"
        ]); // when

        $responseAsserter->assertUnauthorized(); // then

        $responseAsserter->assertJson([
            'error' => UnexpectedAuthentication::class
        ]);
    }
}
