<?php
namespace Suphle\Routing\Documentation;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ApiDocsComponentEntry extends BaseComponentEntry
{
    public function uniqueName(): string
    {
        return "ApiDocsTemplates";
    }

    protected function templatesLocation(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . "Templates";
    }
} 