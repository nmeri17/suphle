<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks\Structures;

use Suphle\Bridge\Laravel\Config\BaseConfigLink;

class SecondLevel extends BaseConfigLink
{
    public function name(): string
    {

        return "Look, an override!";
    }

    public function value(): int
    {

        return $this->nativeValues["value"];
    }
}
