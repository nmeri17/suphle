<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Canary;

use Suphle\Contracts\Routing\CanaryEvaluator;
use Suphle\Contracts\Auth\AuthStorage;

class BetaUserCanary implements CanaryEvaluator
{
    public function __construct(protected readonly AuthStorage $authStorage) {}

    public function willLoad(): ?string
    {
        $userId = $this->authStorage->getId();
        return ($userId && $userId < 1000) ? 'beta' : null;
    }
} 