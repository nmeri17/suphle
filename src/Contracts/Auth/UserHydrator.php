<?php

namespace Suphle\Contracts\Auth;

interface UserHydrator
{
    public function getUserById(string $id): ?UserContract;

    /**
     * @param {criteria}:array pair of email/username/any field you are interested in hydrating user with
    */
    public function findAtLogin(array $criteria): ?UserContract;

    public function setUserModel(UserContract $model): void;
}
