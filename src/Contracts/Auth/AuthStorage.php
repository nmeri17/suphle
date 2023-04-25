<?php

namespace Suphle\Contracts\Auth;

interface AuthStorage
{
    public function logout(): void;

    /**
     * @param {value}: target user identifier
     * @return newly minted token for that id or simply returns same value for session-based mechanism
    */
    public function imitate(string $value): string;

    public function getId(): ?string;

    /**
     * Called during login or when reverting to admin during impersonation
    */
    public function startSession(string $userId): string;

    /**
     * Implementations are advised to set an $identifier property for use by other methods looking to hydrate a user with it
    */
    public function resumeSession(): void;

    /**
     * @return null when there's no authenticated user
    */
    public function getUser(): ?UserContract;

    public function setHydrator(UserHydrator $userHydrator): void;
}
