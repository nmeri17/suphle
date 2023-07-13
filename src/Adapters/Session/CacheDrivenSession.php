<?php

namespace Suphle\Adapters\Session;

use Suphle\Contracts\IO\{EnvAccessor, CacheManager, Session as SessionContract};

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Request\PayloadStorage;

#[BindsAsSingleton(SessionContract::class)]
class CacheDrivenSession implements SessionContract
{
    public const FLASH_KEY = "_flash_entry",

    DEFAULT_COOKIE_NAME = "suphle",

    SESSION_STORAGE_PREFIX = "_session_";

    protected string $cookieValue = "";

    public function __construct (
        protected readonly EnvAccessor $envAccessor,

        protected readonly PayloadStorage $payloadStorage,

        protected readonly CacheManager $cacheManager // for scaling horizontally and because native session is insufferable in a concurrent environment
    ) {
        //
    }

    // not using a single array for all sessions to avoid race conditions
    protected function sessionEntryKey ():string {

        return self::SESSION_STORAGE_PREFIX . $this->getCookieValue();
    }

    public function allSessionEntries(): array
    {
        $cachedContent = $this->cacheManager->getItem(

            $this->sessionEntryKey(), fn () => json_encode([])
        );

        return json_decode($cachedContent, true);
    }

    protected function getCookieValue ():string {

        if (!empty($this->cookieValue)) return $this->cookieValue;

        return $this->cookieValue = $this->payloadStorage->getCookieParams()[self::DEFAULT_COOKIE_NAME] ??

        bin2hex(random_bytes(25));
    }

    public function getValue(string $key)
    {

        return $this->allSessionEntries()[$key];
    }

    public function getOldInput(string $key)
    {

        return $this->getValue(self::FLASH_KEY)[$key];
    }

    public function setValue(string $key, $value): void
    {
        $storage = $this->allSessionEntries();

        $storage[$key] = $value;

        $this->cacheManager->saveItem($this->sessionEntryKey(), $storage);
    }

    public function setFlashValue(string $key, $value): void
    {

        if (!$this->hasKey(self::FLASH_KEY)) $this->resetOldInput();

        $flash = $this->getValue(self::FLASH_KEY);

        $flash[$key] = $value;

        $this->setValue(self::FLASH_KEY, $flash);
    }

    public function hasKey(string $key): bool
    {

        return array_key_exists($key, $this->allSessionEntries());
    }

    public function hasOldInput(string $key): bool
    {

        return $this->hasKey(self::FLASH_KEY) &&

        array_key_exists($key, $this->getValue(self::FLASH_KEY));
    }

    public function resetOldInput(): void
    {

        $this->setValue(self::FLASH_KEY, []);
    }

    public function getAsCookieString ():string {

        $activeCookieId = $this->getCookieValue();

        return implode("", [
        
            self::DEFAULT_COOKIE_NAME. "=". $activeCookieId. ";",

            "path=/; HttpOnly;",

            "Max-Age=". $this->envAccessor->getField("SESSION_DURATION"). ";"
        ]);
    }

    public function reset(): void
    {

        $this->cacheManager->saveItem($this->sessionEntryKey(), []);
    }
}
