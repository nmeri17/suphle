<?php

namespace Suphle\Routing\Analysis;

interface RendererAnalyzerInterface
{
    public function canAnalyze(string $rendererClass): bool;
    
    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array;
    
    public function getContentType(string $rendererClass): string;
}


