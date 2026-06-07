<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix('')]
class BetaUserCanary extends BaseCoordinator
{
    #[Route('/beta')]
    public function beta(): Json
    {
        return new Json(['message' => 'Beta version']);
    }

    public function shouldUseCanary(): bool
    {
        // Mock canary evaluation logic
        return false; // For testing, always return false
    }
} 