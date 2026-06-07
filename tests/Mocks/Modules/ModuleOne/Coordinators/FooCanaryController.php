<?php
namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState};
use Suphle\Response\Format\Json;

use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Canaries\CanaryRequestHasFoo;

#[CanaryState([CanaryRequestHasFoo::class])]
class FooCanaryController extends BaseCoordinator
{
    #[Route("foo-profile")]
    public function fooHandler(): Json
    {
        $canaryState = $this->requestDetails->getCanaryState();
        return match($canaryState) {
            'foo' => new Json(['profile' => 'FOO user profile!']),
            default => new Json(['profile' => 'STABLE user profile.'])
        };
    }
} 