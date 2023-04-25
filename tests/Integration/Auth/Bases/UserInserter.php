<?php

namespace Suphle\Tests\Integration\Auth\Bases;

use Suphle\Testing\Condiments\DirectHttpTest;

use Suphle\Contracts\Auth\UserContract;

/**
 * Helper trait for adding a fresh user, then using his details for login
*/
trait UserInserter
{
    use DirectHttpTest;

    private $correctPassword = "correct";
    private $incorrectPassword = "incorrect";

    public function getInsertedUser(string $password): UserContract
    {

        return $this->replicator->modifyInsertion(1, [ // inserting a new row rather than pulling a random one so we can access the "password" field during login request

            "password" => password_hash($password, PASSWORD_DEFAULT)
        ])->first();
    }

    public function sendCorrectRequest(string $loginPath): void
    {

        $user = $this->getInsertedUser($this->correctPassword);

        $this->setJsonParams($loginPath, [

            "email" => $user->email,

            "password" => $this->correctPassword
        ], "post");
    }

    public function sendIncorrectRequest(string $loginPath): void
    {

        $user = $this->getInsertedUser($this->correctPassword);

        $this->setJsonParams($loginPath, [

            "email" => $user->email,

            "password" => $this->incorrectPassword
        ], "post");
    }

    public function getLoginPath(): string
    {

        return $this->loginPath;
    }
}
