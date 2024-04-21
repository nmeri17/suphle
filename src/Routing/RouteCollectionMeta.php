<?php

namespace Suphle\Routing;

use Suphle\Routing\Structures\CollectionMetaExclusion;

abstract class RouteCollectionMeta
{
    protected array $registry = [];
    protected array $excludePatterns = [];
    protected array $interactedPatterns = [];

    public function tagPatterns(CollectionMetaFunnel $collector): self
    {

        $this->registry[] = $collector;

        return $this;
    }

    public function updateInteractedPatterns(string $pattern): void
    {

        $this->interactedPatterns[] = $pattern;
    }

    public function setAllInteractedPatterns (array $patterns):void {

    	$this->interactedPatterns = $patterns;
    }

    public function removeTag(
        array $patternsToOmit,
        string $funnelName,
        callable $matcher = null
    ): self {

        foreach ($patternsToOmit as $pattern) {

            $this->excludePatterns[$pattern] = new CollectionMetaExclusion($funnelName, $matcher);
        }

        return $this;
    }

    /**
     * @param {interactedPatterns}: When omitted, defaults to routed patterns for the request
     * @return CollectionMetaFunnel[] relevant to current path
    */
    public function getFunnelsForInteracted(?array $interactedPatterns = null): array
    {
    	$patternsToScan = $interactedPatterns ?? $this->interactedPatterns;

        $toWeedOut = array_intersect(
            
            $patternsToScan, array_keys($this->excludePatterns)
        );

        return array_filter($this->registry, function (CollectionMetaFunnel $funnel) use ($toWeedOut, $patternsToScan) {

            $boundToInteracted = false;

            foreach ($patternsToScan as $pattern) {

                if ($funnel->containsPattern($pattern)) {

                    $boundToInteracted = true;

                    break;
                }
            }

            if (!$boundToInteracted) {
                return false;
            }

            foreach ($toWeedOut as $pattern) {

                if ($this->excludePatterns[$pattern]->shouldExclude($funnel)) {

                    return false;
                }
            }

            return true;
        });
    }

    public function emptyAllStacks(): void
    {

        $this->interactedPatterns = $this->excludePatterns = $this->registry = [];
    }
}
