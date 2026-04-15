<?php

namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\{Markup, Redirect};

trait AnalyzerUtils
{
    protected function getStandardFormatSchema(string $rendererClass): ?array
    {
        if (is_subclass_of($rendererClass, Markup::class)) return ["type" => "string", "format" => "html"];
        if (is_subclass_of($rendererClass, Redirect::class)) return ["type" => "string", "description" => "HTTP Redirect"];
        return null;
    }
}