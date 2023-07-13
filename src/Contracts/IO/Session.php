<?php

namespace Suphle\Contracts\IO;

interface Session
{
    public function setValue(string $key, $value): void;

    public function getValue(string $key);

    public function hasKey(string $key): bool;

    public function reset(): void;

    public function getAsCookieString ():string;

    public function hasOldInput(string $key): bool;

    public function getOldInput(string $key);

    public function setFlashValue(string $key, $value): void;

    public function resetOldInput(): void;

    public function allSessionEntries(): array;
}
