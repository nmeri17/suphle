<?php

namespace Suphle\Exception;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ComponentEntry extends BaseComponentEntry
{
    public function uniqueName(): string
    {

        return "SuphleErrorTemplates";
    }

    protected function templatesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
    }
}
