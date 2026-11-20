<?php

namespace Suphle\Tests\Integration\Authorization;

class NonAdminPathAuthorizerTest extends TestPathAuthorizer
{
    protected $user;

    protected function setUser(): void
    {

        $this->user = $this->makeUser();
    }

    public function test_absent_nested_authorization_fails()
    {

        $this->actingAs($this->user); // given

        $this->get($this->expandUrl("retain")) // when

        ->assertForbidden(); // then
    }

    public function test_unlock_works()
    {

        $this->actingAs($this->user); // given

        $this->get($this->expandUrl("secede")) // when

        ->assertOk(); // then
    }
}
