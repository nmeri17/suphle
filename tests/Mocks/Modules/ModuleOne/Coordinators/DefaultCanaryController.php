<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;
use Suphle\Request\RequestDetails;
use Suphle\Contracts\IO\Session;

#[CanaryState([\Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\DefaultCanary::class])]
class DefaultCanaryController extends ServiceCoordinator
{
    public function __construct(
        Session $sessionClient,
        protected RequestDetails $requestDetails
    ) {
        parent::__construct($sessionClient);
    }
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