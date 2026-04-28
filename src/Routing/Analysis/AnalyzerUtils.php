<?php
namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\{Markup, Redirect, Json};
use Suphle\Request\PayloadStorage;

trait AnalyzerUtils
{
    public function getStandardFormatSchema(string $rendererClass): ?array
    {
        if (is_subclass_of($rendererClass, Markup::class)) {
            return [
                'type' => 'string',
                'contentMediaType' => PayloadStorage::HTML_HEADER_VALUE,
            ];
        }

        if (is_subclass_of($rendererClass, Json::class)) {
            return [
                'type' => 'object',
                'contentMediaType' => PayloadStorage::JSON_HEADER_VALUE
            ];
        }

        if (is_subclass_of($rendererClass, Redirect::class)) {
            return [
                'type' => 'string',
                'description' => 'HTTP Redirect'
            ];
        }

        return null;
    }
}