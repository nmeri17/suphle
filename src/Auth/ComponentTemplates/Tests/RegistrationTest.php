<?php
namespace _modules_shell\_module_name\Tests\Auth;

use Suphle\Testing\Condiments\MailAsserter;

use _database_namespace_\User;

class RegistrationTest extends BaseAuthTest {

    use MailAsserter;

    protected const REGISTER_URL = "/auth/register";

    public function test_user_can_register(): void
    {
        $email = "newuser@example.com"; // given

        $this->post(self::REGISTER_URL, [ // when
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

        $this->post(self::REGISTER_URL, [
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

        $this->post(self::REGISTER_URL, [
            "name"                  => "Dupe User",
            "email"                 => $existing->email,
            "password"              => "password123",
            "password_confirmation" => "password123",
        ])->assertStatus(422);
    }
}