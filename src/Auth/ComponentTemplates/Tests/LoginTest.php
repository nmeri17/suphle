<?php
namespace _modules_shell\_module_name\Tests\Auth;

use Suphle\Auth\Storage\TokenStorage;

use Suphle\Testing\Proxies\SecureUserAssertions;

class LoginTest extends BaseAuthTest {

    use SecureUserAssertions;

    protected const LOGIN_ROUTE = '/auth/login';

    public function test_user_can_login_with_valid_credentials(): void
    {
        $password = 'password123';

        $user = $this->replicator->modifyInsertion(1, [
            'password'          => password_hash($password, PASSWORD_BCRYPT),
            'email_verified_at' => now(),
        ])[0];

        $this->post(self::LOGIN_ROUTE, [ // when
            'email'    => $user->email,
            'password' => $password,
        ])->assertSessionMissing(["errors"])->assertRedirect(); // then — dashboard

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->replicator->modifyInsertion(1, [
            'password' => password_hash('correctpassword', PASSWORD_BCRYPT),
        ])[0];

        $this->post(self::LOGIN_ROUTE, [
            'email'    => $user->email,
            'password' => 'wrongpassword',
        ])->assertStatus(422); // then — validation/auth failure
    }

    public function test_api_login_returns_token(): void
    {
        $password = 'password123';

        $user = $this->replicator->modifyInsertion(1, [ // given
            'password'          => password_hash($password, PASSWORD_BCRYPT),
            'email_verified_at' => now(),
        ])[0];

        $response = $this->postJson('/api/v1'. self::LOGIN_ROUTE, [ // when
            'email'    => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
        ->assertJsonStrucure(['token']); // then

        $this->assertAuthenticatedAs($user, TokenStorage::class);
    }

    public function test_api_login_fails_with_invalid_credentials(): void
    {
        $this->postJson('/api/v1'. self::LOGIN_ROUTE, [
            'email'    => 'nobody@nowhere.com',
            'password' => 'wrongpassword',
        ])->assertStatus(401);
    }

    public function test_logout() {

        $user = $this->replicator->getRandomEntity();

        $this->actingAs($user); // given

        $this->post("/auth/logout"); // when

        $this->assertGuest(); // then
    }
}