<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Canary;

use Suphle\Contracts\Routing\CanaryEvaluator;
use Suphle\Contracts\Auth\AuthStorage;

class SpecialUserCanary implements CanaryEvaluator
{
    public function __construct(protected readonly AuthStorage $authStorage) {}

    public function willLoad(): ?string
    {
        // Example: Only users with a special flag
        $user = $this->authStorage->getUser();
        return ($user && $user->isSpecial()) ? 'special' : null;
    }
} 