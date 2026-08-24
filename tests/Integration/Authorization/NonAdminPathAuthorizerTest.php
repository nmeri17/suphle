<?php

namespace Suphle\Tests\Integration\Authorization;

class NonAdminPathAuthorizerTest extends TestPathAuthorizer
{
    private $user;

    protected function setUser(): void
    {

        $this->user = $this->makeUser();
    }

    public function test_absent_nested_authorization_fails()
    {

        $this->actingAs($this->user); // given

        $this->get("/employment/retain"); // when

        $this->assertFalse($this->authorizationSuccess()); // then
    }

    public function test_unlock_works()
    {

        $this->actingAs($this->user); // given

        $this->get("/employment/secede"); // when

        $this->assertTrue($this->authorizationSuccess()); // then
    }
}
