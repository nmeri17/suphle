<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\DefaultCanary;

#[CanaryState([DefaultCanary::class])]
class DefaultCanaryController extends BaseCoordinator
{
    #[Route("default-profile", method: HttpMethod::GET)]
    public function defaultHandler(): Json
    {
        return new Json(['profile' => 'STABLE user profile.']);
    }

    #[Route("profile/{id}", method: HttpMethod::GET)]
    public function defaultPlaceholder(): Json
    {
        return new Json(['profile' => 'STABLE user profile.']);
    }
} 