<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Config;

use Suphle\Contracts\Config\Router;

class RouterMock implements Router
{
    public function getCoordinatorPath(): string
    {
        return "Coordinators";
    }

    public function getCoordinatorClassesToScan(): array
    {
        return [];
    }

    public function mirrorAuthenticator(): string
    {
        return "";
    }
}
