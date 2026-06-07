<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;
use Suphle\Request\RequestDetails;
use Suphle\Contracts\IO\Session;
use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\CanaryForUser5;

#[CanaryState([CanaryForUser5::class])]
class User5CanaryController extends BaseCoordinator
{
    #[Route("user5-profile", method: HttpMethod::GET)]
    public function user5Handler(): Json
    {
        $canaryState = $this->requestDetails->getCanaryState();
        return match($canaryState) {
            'user5' => new Json(['profile' => 'USER5 user profile!']),
            default => new Json(['profile' => 'STABLE user profile.'])
        };
    }
} 