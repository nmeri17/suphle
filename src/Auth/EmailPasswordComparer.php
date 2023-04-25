<?php

namespace Suphle\Auth;

use Suphle\Contracts\Database\OrmDialect;

use Suphle\Contracts\Auth\{UserContract, ColumnPayloadComparer, UserHydrator as HydratorContract};

use Suphle\Request\PayloadStorage;

class EmailPasswordComparer implements ColumnPayloadComparer
{
    protected HydratorContract $userHydrator;

    protected ?UserContract $user;

    protected string $columnIdentifier = "email";

    public function __construct(OrmDialect $ormDialect, protected readonly PayloadStorage $payloadStorage)
    {

        $this->userHydrator = $ormDialect->getUserHydrator();
    }

    protected function findMatchingUser(): ?UserContract
    {

        return $this->userHydrator->findAtLogin([

            $this->columnIdentifier => $this->payloadStorage->getKey($this->columnIdentifier)
        ]);
    }

    public function compare(): bool
    {

        $user = $this->findMatchingUser();

        $password = $this->payloadStorage->getKey("password");

        if (
            is_null($user) ||

            !password_verify((string) $password, (string) $user->getPassword())
        ) {

            return false;
        }

        $this->user = $user;

        return true;
    }

    public function getUser(): UserContract
    {

        return $this->user;
    }
}
