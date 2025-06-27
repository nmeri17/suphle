<?php

namespace Suphle\Routing;

abstract class CollectionMetaFunnel
{
    public function __construct(protected readonly array $activePatterns)
    {

        //
    }

    // this might have to be refactored to work sequentially ie not return true for identical names in perhaps a different collection, with a different tag. Currently, they're unique and isolated but should be contextualized to their parents
    public function containsPattern(string $pattern): bool
    {

        return in_array($pattern, $this->activePatterns);
    }
}
