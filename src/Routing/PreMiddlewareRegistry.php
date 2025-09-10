<?php

namespace Suphle\Routing;

use Suphle\Services\Decorators\BindsAsSingleton;
use Suphle\Middleware\Collectors\CollectionMetaFunnel;

#[BindsAsSingleton]
class PreMiddlewareRegistry
{
    protected array $taggedPatterns = [];

    public function tagPatterns(CollectionMetaFunnel $collector): self
    {
        $this->taggedPatterns[] = $collector;
        return $this;
    }

    public function removeTag(array $patterns, string $collectorClass): self
    {
        // Remove specific patterns from a collector
        foreach ($this->taggedPatterns as $index => $collector) {
            if (get_class($collector) === $collectorClass) {
                // Remove the patterns from this collector
                $collector->removePatterns($patterns);
            }
        }
        return $this;
    }

    public function getTaggedPatterns(): array
    {
        return $this->taggedPatterns;
    }
} 