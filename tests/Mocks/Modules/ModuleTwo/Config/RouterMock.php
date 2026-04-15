<?php

namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Config;

use Suphle\Config\Router;

class RouterMock extends Router
{
    public function getCoordinatorPath(): string
    {
        return "Coordinators";
    }

    public function getCoordinatorClassesToScan(): array
    {
        return [];
    }
}
