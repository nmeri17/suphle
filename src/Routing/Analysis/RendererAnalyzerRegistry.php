<?php

namespace Suphle\Routing\Analysis;

use Suphle\Services\Decorators\BindsAsSingleton;

#[BindsAsSingleton]
class RendererAnalyzerRegistry
{
    private array $analyzers = [];

    public function __construct()
    {
        $this->registerDefaultAnalyzers();
    }

    public function register(RendererAnalyzerInterface $analyzer): void
    {
        $this->analyzers[] = $analyzer;
    }

    public function getAnalyzer(string $rendererClass): ?RendererAnalyzerInterface
    {
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->canAnalyze($rendererClass)) {
                return $analyzer;
            }
        }

        return null;
    }

    public function analyzeSchema(string $rendererClass, \ReflectionMethod $method): array
    {
        $analyzer = $this->getAnalyzer($rendererClass);
        
        if ($analyzer) {
            return $analyzer->analyzeSchema($rendererClass, $method);
        }

        // Default fallback
        return ['type' => 'unknown'];
    }

    public function getContentType(string $rendererClass): string
    {
        $analyzer = $this->getAnalyzer($rendererClass);
        
        if ($analyzer) {
            return $analyzer->getContentType($rendererClass);
        }

        // Default fallback
        return 'text/html';
    }

    private function registerDefaultAnalyzers(): void
    {
        $this->analyzers = [
            new JsonRendererAnalyzer(),
            new MarkupRendererAnalyzer(),
            new RedirectRendererAnalyzer(),
            new ReloadRendererAnalyzer(),
            new HotwireRendererAnalyzer(),
        ];
    }
}


