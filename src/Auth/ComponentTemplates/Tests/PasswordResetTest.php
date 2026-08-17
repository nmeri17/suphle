<?php
namespace _modules_shell\_module_name\Tests\Auth;

use Suphle\Testing\Condiments\MailAsserter;

use _modules_shell\_module_name\Coordinators\PasswordResetCoordinator;

use _database_namespace_\PasswordResetToken;

// user requests reset. clicking link sent to their mail returns to us the token which is used to hydrate associated user. new password is entered and process is complete
class PasswordResetTest extends BaseAuthTest
{
    use MailAsserter; // check whose setup is going to run

    public function test_reset_request_creates_token_for_existing_user(): void
    {
        $user = $this->replicator->modifyInsertion(1, [
            "email_verified_at" => now(),
        ])[0];

        $userMail = $user->email;

        $this->post("/resets/mail", ["email" => $userMail]); // when

        $this->databaseApi->assertDatabaseHas(PasswordResetToken::TABLE_NAME, [ // then
            "user_id" => $user->id,
        ]);

        $this->assertSentMailTo($userMail);
    }

    public function test_reset_request_fails_for_nonexistent_email(): void
    {
        $this->post("/resets/mail", [
            "email" => "ghost@nowhere.com",
        ])->assertStatus(422); // validation: exists:users,email fails
    }

    public function test_reset_form_loads_with_valid_token(): void
    {
        $user = $this->replicator->modifyInsertion(1)[0];

        $token = bin2hex(random_bytes(32));

        PasswordResetToken::create([
            "user_id" => $user->id,
            "token"   => $token,
        ]);

        $this->get(
            $this->expandUrl(PasswordResetCoordinator::class, "showResetForm", ["token" => $token])
            ) // when
            ->assertStatus(200); // then
    }

    public function test_reset_form_rejects_invalid_token(): void
    {
        $this->get(
            $this->expandUrl(PasswordResetCoordinator::class, "showResetForm", ["token" => "bogustoken"])
        )
        ->assertStatus(404);
    }

    public function test_token_is_deleted_after_use(): void
    {
        $user = $this->replicator->modifyInsertion(1, [ // given user requests reset, clicks the link
            "password" => password_hash("oldpassword", PASSWORD_BCRYPT),
        ])[0];

        $token = bin2hex(random_bytes(32));

        PasswordResetToken::create([
            "user_id" => $user->id,
            "token"   => $token,
        ]);

        $this->post("/resets/new-password", [ // when
            "token"                 => $token,
            "password"              => "newpassword123",
            "password_confirmation" => "newpassword123",
        ])->assertRedirect("/resets/password-updated"); // then

        $this->databaseApi->assertDatabaseMissing(PasswordResetToken::TABLE_NAME, [
            "token" => $token,
        ]);
    }
}