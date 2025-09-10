<?php

namespace Suphle\ComponentTemplates\ApiDocsComponent;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ApiDocsComponentEntry extends BaseComponentEntry
{
    public function uniqueName(): string
    {
        return "SuphleApiDocsTemplates";
    }

    protected function templatesLocation(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . "Templates";
    }
} 