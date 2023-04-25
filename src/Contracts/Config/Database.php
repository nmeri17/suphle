<?php

namespace Suphle\Contracts\Config;

interface Database extends ConfigMarker
{
    public function getCredentials(): array;

    /**
     * Absolute path with trailing slash
    */
    public function componentInstallPath(): string;

    public function componentInstallNamespace(): string;
}
