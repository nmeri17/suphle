<?php

namespace Suphle\Routing\Analysis;

use Suphle\Response\Format\Reload;
use Suphle\Request\PayloadStorage;

class ReloadRendererAnalyzer implements RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool
    {
        return is_subclass_of($rendererClass, Reload::class);
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


