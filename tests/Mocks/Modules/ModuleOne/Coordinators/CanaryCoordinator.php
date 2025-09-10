<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\Canary\BetaUserCanary;

#[CanaryState([BetaUserCanary::class])]
class CanaryCoordinator extends ServiceCoordinator
{
    #[Route('/beta')]
    public function beta(): Json
    {
        $canary = $this->requestDetails->getCanaryState();
        return match ($canary) {
            'beta' => new Json(['beta' => true, 'feature' => 'experimental']),
            default => new Json(['stable' => true, 'feature' => 'production'])
        };
    }

    #[Route('/stable')]
    public function stable(): Json
    {
        return new Json(['stable' => true, 'feature' => 'production']);
    }
} 