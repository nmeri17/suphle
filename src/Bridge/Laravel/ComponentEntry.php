<?php

namespace Suphle\Bridge\Laravel;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ComponentEntry extends BaseComponentEntry
{
    public function uniqueName(): string
    {

        return "SuphleLaravelTemplates";
    }

    protected function templatesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
    }

    public function eject(): void
    {

        parent::eject();

        @mkdir($this->userLandMirror(). "bootstrap/cache");
    }
}
