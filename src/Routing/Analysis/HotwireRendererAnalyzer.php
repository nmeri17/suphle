<?php

namespace Suphle\Routing\Analysis;

use Suphle\Adapters\Presentation\Hotwire\Formats\{BaseHotwireStream, RedirectHotwireStream, ReloadHotwireStream};

class HotwireRendererAnalyzer implements RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool
    {
        return is_subclass_of($rendererClass, BaseHotwireStream::class);
    }
    
    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array
    {
        if (is_subclass_of($rendererClass, RedirectHotwireStream::class)) {
            return [
                'type' => 'string',
                'format' => 'html',
                'description' => 'Hotwire redirect stream'
            ];
        }

        if (is_subclass_of($rendererClass, ReloadHotwireStream::class)) {
            return [
                'type' => 'string',
                'format' => 'html',
                'description' => 'Hotwire reload stream'
            ];
        }

        // Generic Hotwire stream
        return [
            'type' => 'string',
            'format' => 'html',
            'description' => 'Hotwire stream response'
        ];
    }
    
    public function getContentType(string $rendererClass): string
    {
        return BaseHotwireStream::TURBO_INDICATOR;
    }
}


