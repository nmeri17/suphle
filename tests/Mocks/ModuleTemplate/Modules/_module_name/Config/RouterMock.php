<?php

namespace Suphle\Tests\Mocks\Modules\_module_name\Config;

use Suphle\Config\Router;

class RouterMock extends Router
{
    public function getCoordinatorPath(): string
    {
        return "Controllers";
    }

    public function getCoordinatorClassesToScan(): array
    {
        return [
            // List specific coordinator classes to scan, or empty array for all
        ];
    }
}
