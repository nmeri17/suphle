<?php

namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Redirect;
use Suphle\Request\PayloadStorage;

class RedirectRendererAnalyzer implements RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool
    {
        return is_subclass_of($rendererClass, Redirect::class);
    }
    
    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array
    {
        return [
            'type' => 'string',
            'description' => 'Empty response with Location header'
        ];
    }
    
    public function getContentType(string $rendererClass): string
    {
        return PayloadStorage::HTML_HEADER_VALUE; // Redirects don't have content, but default to HTML
    }
}


