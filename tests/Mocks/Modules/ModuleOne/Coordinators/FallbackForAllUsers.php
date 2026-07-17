<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState, RoutePrefix};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\FallbackForAllUsers as FallbackCanary;

#[RoutePrefix("/canary")]
#[CanaryState([FallbackCanary::class])]
class FallbackForAllUsers
{
    #[Route("/secure")]
    public function secureRoute(): Json
    {
        return new Json(['secure' => false, 'fallback' => true, 'message' => 'Using stable version']);
    }
} 