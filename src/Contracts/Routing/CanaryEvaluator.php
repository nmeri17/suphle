<?php

namespace Suphle\Contracts\Routing;

interface CanaryEvaluator
{
    /**
     * Return a string (canary state) if this canary is active, or null if not.
     */
    public function willLoad(): ?string;
} 