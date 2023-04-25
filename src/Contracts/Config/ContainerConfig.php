<?php

namespace Suphle\Contracts\Config;

use Suphle\Contracts\Hydration\ExternalPackageManager;

interface ContainerConfig extends ConfigMarker
{
    public function containerLogFile(): string;

    /**
     * @return string<ExternalPackageManager>[]
    */
    public function getExternalHydrators(): array;
}
