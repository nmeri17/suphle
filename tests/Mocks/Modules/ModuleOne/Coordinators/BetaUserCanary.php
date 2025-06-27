<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class BetaUserCanary extends BaseCoordinator
{
    #[Route('/beta', method: HttpMethod::GET)]
    public function betaFeature(): Json
    {
        return new Json(['beta' => true, 'feature' => 'experimental']);
    }

    public function shouldUseCanary(): bool
    {
        // Mock canary evaluation logic
        return false; // For testing, always return false
    }
} 