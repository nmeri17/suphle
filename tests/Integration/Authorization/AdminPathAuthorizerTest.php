<?php

namespace Suphle\Tests\Integration\Authorization;

class AdminPathAuthorizerTest extends TestPathAuthorizer
{
    private $user;

    protected function setUser(): void
    {

        $this->user = $this->makeUser(true);
    }

    public function test_present_authorization_succeeds()
    {

        $this->actingAs($this->user); // given

        $this->get($this->expandUrl("adminEntry")) // when

        ->assertOk(); // then
    }

    public function test_present_nested_authorization_succeeds()
    {

        $this->actingAs($this->user); // given

        $this->get($this->expandUrl("retain")) // when

        ->assertOk(); // then
    }
}
