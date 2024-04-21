<?php

namespace Suphle\Testing\Proxies\Extensions;

use Suphle\Middleware\{MiddlewareRegistry, PatternMiddleware};

class MiddlewareManipulator extends MiddlewareRegistry
{
    protected bool $stackAlwaysEmpty = false;

    protected array $preExclude = [];
    protected array $preInclude = [];

    /**
     * Whenever router decides on the active pattern, it'll ultimately include middlewares applied here
     *
     * We're using this instead of updating the default middleware list, since the eventual module may have custom config we are unwilling to override with whatever mock we'll set as default
     *
     * @param {collectors} CollectionMetaFunnel[]
    */
    public function addToActiveStack(array $collectors): void
    {

        $this->preInclude = $collectors;
    }

    public function disableAll(): void
    {

        $this->stackAlwaysEmpty = true;
    }

    /**
     * @param {collectorNames} CollectionMetaFunnel::class[]
    */
    public function disableCollectors(array $collectorNames): void
    {

        $this->preExclude = $collectorNames;
    }

    /**
     * {@inheritdoc}
    */
    public function getFunnelsForInteracted(?array $interactedPatterns = null): array
    {

        if ($this->stackAlwaysEmpty) {
            return [];
        }

        $stack = $this->preInclude;

        $parentStack = parent::getFunnelsForInteracted($interactedPatterns);

        foreach ($parentStack as $index => $collector) {

            if (in_array($collector::class, $this->preExclude)) {

                unset($parentStack[$index]);
            }
        }

        return [...$stack, ...$parentStack];
    }
}
