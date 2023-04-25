<?php

namespace Suphle\Auth\Repositories;

use Suphle\Contracts\Auth\{LoginActions, ColumnPayloadComparer};

abstract class BaseAuthRepo implements LoginActions
{
    /**
     * Expects sub-classes to inject an ColumnPayloadComparer $comparer. Can't set the property here to avoid visibility headaches
    */
    public function compareCredentials(): bool
    {

        return $this->comparer->compare();
    }

    public function failedLogin(): iterable
    {

        return ["message" => "Incorrect credentials"];
    }

    protected function startSessionForCompared(): string
    {

        return $this->authStorage->startSession(
            $this->comparer->getUser()->getId()
        );
    }
}
