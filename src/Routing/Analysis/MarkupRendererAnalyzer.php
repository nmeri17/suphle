<?php

namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Markup;
use Suphle\Request\PayloadStorage;

class MarkupRendererAnalyzer implements RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool
    {
        return is_subclass_of($rendererClass, Markup::class);
    }
    
    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array
    {
        return [
            'type' => 'string',
            'format' => 'html'
        ];
    }
    
    public function getContentType(string $rendererClass): string
    {
        return PayloadStorage::HTML_HEADER_VALUE;
    }
}


