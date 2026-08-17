<?php
namespace _modules_shell\_module_name\Tests\Auth;

use _modules_shell\_module_name\Coordinators\{BrowserAuthCoordinator, ApiAuthCoordinator};
use _database_namespace_\User;
use DateTime;

class VerificationTest extends BaseAuthTest {

    public function test_user_can_verify_email_with_valid_token(): void
    {
        $token = $this->getToken();

        $user = $this->replicator->modifyInsertion(1, [
            "verification_token" => $token,
            "email_verified_at"  => null,
        ])[0];

        $this->get($this->getVerifyUrl($token)) // when
            ->assertSessionMissing(["errors"])->assertRedirect(); // then — redirects to login/dashboard

        $this->databaseApi->assertDatabaseHas(User::TABLE_NAME, [
            "id"                 => $user->id,
            "verification_token" => null,
            ["email_verified_at", "!=", null]
        ]);
    }

    protected function getVerifyUrl (string $token):string {

        return $this->routeReader->expandRoute(
            BrowserAuthCoordinator::class, "verifyEmail", compact("token")
        );
    }

    protected function getToken ():string {

        return bin2hex(random_bytes(32));
    }

    public function test_verification_fails_with_invalid_token(): void
    {

        $this->get($this->getVerifyUrl("invalidtoken123"))
            ->assertStatus(404);
    }

    public function test_double_verification_is_rejected(): void
    {
        $token = $this->getToken();

        $this->replicator->modifyInsertion(1, [
            "verification_token" => $token,
            "email_verified_at"  => (new DateTime)->format("Y-m-d H:i:s"), // already verified
        ]);

        $this->get($this->getVerifyUrl($token))
            ->assertStatus(404); // then
    }

    public function test_api_verify_email(): void
    {
        $token = $this->getToken();

        $this->replicator->modifyInsertion(1, [
            "verification_token" => $token,
            "email_verified_at"  => null,
        ]);

        $url = $this->routeReader->expandRoute(ApiAuthCoordinator::class, "apiVerifyEmail");

        $this->postJson($url, ["token" => $token]) // when
            ->assertStatus(200);
    }
}