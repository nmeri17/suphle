<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;

#[CanaryState([\Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\CanaryRequestHasFoo::class])]
class FooCanaryController extends ServiceCoordinator
{
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