<?php
namespace Suphle\Auth;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ComponentEntry extends BaseComponentEntry
{
    public function uniqueName(): string
    {

        return "SuphleIdentity";
    }

    protected function templatesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
    }
}
