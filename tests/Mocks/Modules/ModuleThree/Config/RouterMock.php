<?php

namespace Suphle\Tests\Mocks\Modules\ModuleThree\Config;

use Suphle\Config\Router;

use Suphle\Tests\Mocks\Modules\ModuleThree\Routes\BrowserCollection;

class RouterMock extends Router
{
    public function browserEntryRoute(): ?string
    {

        return BrowserCollection::class;
    }
}
