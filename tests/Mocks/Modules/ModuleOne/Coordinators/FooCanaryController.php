<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;
use Suphle\Request\RequestDetails;
use Suphle\Contracts\IO\Session;

#[CanaryState([\Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\CanaryRequestHasFoo::class])]
class FooCanaryController extends ServiceCoordinator
{
    public function __construct(
        Session $sessionClient,
        protected RequestDetails $requestDetails
    ) {
        parent::__construct($sessionClient);
    }
    #[Route("foo-profile", method: HttpMethod::GET)]
    public function fooHandler(): Json
    {
        $canaryState = $this->requestDetails->getCanaryState();
        return match($canaryState) {
            'foo' => new Json(['profile' => 'FOO user profile!']),
            default => new Json(['profile' => 'STABLE user profile.'])
        };
    }
} 