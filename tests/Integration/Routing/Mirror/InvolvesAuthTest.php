<?php

namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Auth\Storage\{TokenStorage, SessionStorage};

use Suphle\Tests\Integration\Auth\AuthTestBase;

class InvolvesAuthTest extends AuthTestBase
{
    public function test_mirrored_route_detects_auth()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, SessionStorage::class); // given

        $this->get($this->mirrorDashboardUrl()) // when

        ->assertUnauthorized(); // then
    }

    protected function mirrorDashboardUrl ():string {

        return $this->expandUrl("dashboard", [], null, true);
    }

    public function test_auth_storage_changes()
    {

        $tokenClass = TokenStorage::class;

        $requestToken = $this->actingAs($this->replicator->getRandomEntity(), $tokenClass); // given

        $this->get($this->mirrorDashboardUrl(), [], [

            TokenStorage::AUTHORIZATION_HEADER => "Bearer ". $requestToken
        ]) // when
        ->assertOk(); // then

        $this->assertInstanceOf($tokenClass, $this->getAuthStorage());
    }
}
