<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Canary;

use Suphle\Response\Format\Json;
use Suphle\Contracts\Routing\CanaryEvaluator;

class FallbackForAllUsers implements CanaryEvaluator
{
    public function betaFeature(): Json
    {
        return new Json(['feature' => 'stable', 'status' => 'available']);
    }

    public function willLoad(): ?string
    {
        // Always fallback if no other canary matches
        return 'fallback';
    }
} 