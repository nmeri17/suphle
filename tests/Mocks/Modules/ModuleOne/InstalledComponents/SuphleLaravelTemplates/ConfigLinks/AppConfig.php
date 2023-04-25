<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks;

use Suphle\Bridge\Laravel\Config\BaseConfigLink;

class AppConfig extends BaseConfigLink
{
    public function name(): string
    {

        return "Look, an override!";
    }
}
