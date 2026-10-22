<?php
namespace _modules_shell\_module_name\Tests\Auth;

use Suphle\Auth\Storage\TokenStorage;

use _modules_shell\_module_name\Coordinators\BrowserAuthCoordinator;

use Suphle\Testing\Proxies\SecureUserAssertions;

class LoginTest extends AuthTestBase {

    use SecureUserAssertions;

    protected string $coordinatorName = BrowserAuthCoordinator::class;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = "password123";

        $user = $this->replicator->modifyInsertion(1, [
            "password"          => password_hash($password, PASSWORD_BCRYPT),
            "email_verified_at" => now(),
        ])[0];

        $this->post($this->expandUrl("handleLogin"), [ // when
            "email"    => $user->email,
            "password" => $password,
        ])->assertSessionMissing(["errors"])->assertRedirect(); // then — dashboard

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->replicator->modifyInsertion(1, [
            "password" => password_hash("correctpassword", PASSWORD_BCRYPT),
        ])[0];

        $this->post($this->expandUrl("handleLogin"), [
            "email"    => $user->email,
            "password" => "wrongpassword",
        ])->assertStatus(422); // then — validation/auth failure
    }

    public function test_api_login_returns_token(): void
    {
        $password = "password123";

        $user = $this->replicator->modifyInsertion(1, [ // given
            "password"          => password_hash($password, PASSWORD_BCRYPT),
            "email_verified_at" => now(),
        ])[0];

        $response = $this->postJson($this->expandUrl("handleLogin", [], null, true), [ // when
            "email"    => $user->email,
            "password" => $password,
        ]);

        $response->assertStatus(200)
        ->assertJsonStrucure(["token"]); // then

        $this->assertAuthenticatedAs($user, TokenStorage::class);
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $this->postJson($this->expandUrl("handleLogin", [], null, true), [
            "email"    => "nobody@nowhere.com",
            "password" => "wrongpassword",
        ])->assertStatus(401);
    }

    public function test_logout() {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user); // given

        $this->post($this->expandUrl("logout")); // when

        $this->assertGuest(); // then
    }
}