<?php

namespace Suphle\Tests\Integration\Routing\Mirror;

use Suphle\Auth\Storage\{TokenStorage, SessionStorage};

use Suphle\Tests\Integration\Auth\BaseAuthTest;

class InvolvesAuthTest extends BaseAuthTest
{
    public function test_mirrored_route_detects_auth()
    {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user, SessionStorage::class); // given

        $this->get("/api/v1/segment") // when

        ->assertUnauthorized(); // then
    }

    public function test_auth_storage_changes()
    {

        $tokenClass = TokenStorage::class;

        $requestToken = $this->actingAs($this->replicator->getRandomEntity(), $tokenClass); // given

        $this->get("/api/v1/segment", [], [

            TokenStorage::AUTHORIZATION_HEADER => "Bearer ". $requestToken
        ]) // when
        ->assertOk(); // then

        $this->assertInstanceOf($tokenClass, $this->getAuthStorage());
    }
}
