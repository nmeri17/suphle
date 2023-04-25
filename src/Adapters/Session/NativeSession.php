<?php

namespace Suphle\Adapters\Session;

use Suphle\Contracts\IO\{Session as SessionContract, EnvAccessor};

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton(SessionContract::class)]
class NativeSession implements SessionContract
{
    public const FLASH_KEY = "_flash_entry";

    public function __construct(protected readonly EnvAccessor $envAccessor)
    {

        $this->safeToStart();

        $this->prolongSession();
    }

    /**
     * Avoid "session already started" errors. The superglobal must wait for this to be called before it can be accessed. Otherwise, all data from preceding request will be lost
     */
    protected function safeToStart(): void
    {

        if (
            session_status() == PHP_SESSION_NONE // sessions are enabled but none exists

            && !headers_sent()
        ) {
            session_start();
        }
    }

    public function setValue(string $key, $value): void
    {

        $_SESSION[$key] = $value;
    }

    public function getValue(string $key)
    {

        return $_SESSION[$key];
    }

    public function allSessionEntries(): array
    {

        return $_SESSION;
    }

    public function getOldInput(string $key)
    {

        return $this->getValue(self::FLASH_KEY)[$key];
    }

    public function setFlashValue(string $key, $value): void
    {

        if (!$this->hasKey(self::FLASH_KEY)) {

            $this->resetOldInput();
        }

        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    public function hasKey(string $key): bool
    {

        return array_key_exists($key, $_SESSION);
    }

    public function hasOldInput(string $key): bool
    {

        return $this->hasKey(self::FLASH_KEY) &&

        array_key_exists($key, $this->getValue(self::FLASH_KEY));
    }

    public function resetOldInput(): void
    {

        $_SESSION[self::FLASH_KEY] = [];
    }

    public function reset(): void
    {

        $_SESSION = [];

        session_destroy();
    }

    public function prolongSession(array $cookieOptions = []): void
    {

        @setcookie( // muting a possible notice here since the rr server itself gets spun up in an isolated process by an automated test, where it's impossible to replace with the in-memory version
            session_name(),
            session_id(),
            array_merge([

                "expires" => time() +

                intval($this->envAccessor->getField("SESSION_DURATION"))
            ], $cookieOptions)
        );
    }
}
