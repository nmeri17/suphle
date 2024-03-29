<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks\Structures;

use Suphle\Bridge\Laravel\Config\BaseConfigLink;

class FirstLevel extends BaseConfigLink
{
    public function second_level(): SecondLevel
    {

        return new SecondLevel($this->nativeValues["second_level"]);
    }
}
