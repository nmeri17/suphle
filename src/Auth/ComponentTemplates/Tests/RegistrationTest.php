<?php
namespace _modules_shell\_module_name\Tests\Auth;

use _modules_shell\_module_name\Coordinators\BrowserAuthCoordinator;

use Suphle\Testing\Condiments\MailAsserter;

use _database_namespace\User;

class RegistrationTest extends AuthTestBase {

    use MailAsserter;

    protected string $coordinatorName = BrowserAuthCoordinator::class;

    public function test_user_can_register(): void
    {
        $email = "newuser@example.com"; // given

        $this->post($this->expandUrl("handleRegister"), [ // when
            "name"                  => "Test User",
            "email"                 => $email,
            "password"              => "password123",
            "password_confirmation" => "password123",
        ]);

        // then
        $this->databaseApi->assertDatabaseHas(User::TABLE_NAME, ["email" => $email]);
    }

    public function test_registration_sends_verification_token(): void
    {
        $email = "tokenuser@example.com"; // given

        $this->post($this->expandUrl("handleRegister"), [
            "name"                  => "Token User",
            "email"                 => $email,
            "password"              => "password123",
            "password_confirmation" => "password123",
        ]);

        $this->databaseApi->assertDatabaseHas(User::TABLE_NAME, [
            "email"              => $email,
            "email_verified_at"  => null,
            ["verification_token", "!=", null]
        ]);

        $this->assertSentMailTo($email);
    }

    public function test_duplicate_email_fails_registration(): void
    {
        $existing = $this->replicator->getRandomEntity();

        $this->post($this->expandUrl("handleRegister"), [
            "name"                  => "Dupe User",
            "email"                 => $existing->email,
            "password"              => "password123",
            "password_confirmation" => "password123",
        ])->assertStatus(422);
    }
}