<?php

namespace Suphle\Auth\Storage;

use Suphle\Contracts\IO\Session;

use Suphle\Contracts\Config\AuthContract;

class SessionStorage extends BaseAuthStorage
{
    protected string $identifierKey = "suphle_user_id";
    protected string $previousUserKey = "previous_user";

    protected bool $isImpersonating = false;

    public function __construct(protected readonly Session $sessionClient)
    {

        //
    }

    /**
     * {@inheritdoc}
    */
    public function startSession(string $value): string
    {

        if (!$this->isImpersonating) { // protection against session fixation

            $this->logout();

            $this->sessionClient->prolongSession();
        }

        $this->sessionClient->setValue($this->identifierKey, $value);

        return $this->getId(); // trigger resumption
    }

    /**
     * {@inheritdoc}
    */
    public function resumeSession(): void
    {

        $this->identifier = $this->sessionClient->getValue($this->identifierKey);
    }

    /**
     * {@inheritdoc}
    */
    public function imitate(string $value): string
    {

        $this->setPreviousUser();

        $this->isImpersonating = true;

        return parent::imitate($value);
    }

    protected function setPreviousUser(): void
    {

        if (!$this->hasActiveAdministrator()) {

            $this->sessionClient->setValue($this->previousUserKey, $this->identifier);
        }
    }

    public function getPreviousUser(): ?string
    {

        return $this->sessionClient->getValue($this->previousUserKey);
    }

    public function hasActiveAdministrator(): bool
    {

        return $this->sessionClient->hasKey($this->previousUserKey);
    }

    public function logout(): void
    {

        parent::logout();

        $this->sessionClient->reset();
    }
}
