<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, CanaryState, RoutePrefix};
use Suphle\Response\Format\Json;
use Suphle\Tests\Mocks\Modules\ModuleOne\Canary\{BetaUserCanary, CanaryRequestHasFoo, CanaryForUser5};

#[RoutePrefix("/canary")]
#[CanaryState([BetaUserCanary::class, CanaryRequestHasFoo::class, CanaryForUser5::class])]
class CanaryCoordinator extends BaseCoordinator
{
    #[Route("/beta")]
    public function beta(): Json
    {
        $data = match ($this->requestDetails->getCanaryState()) {
            "beta" => ["beta" => true, "feature" => "experimental"],
            "foo" => ["profile" => "FOO user profile!"],
            'user5' => ['profile' => 'USER5 user profile!'],
            default => ["stable" => true, "feature" => "production"]
        };

        return new Json($data);
    }

    #[Route("/stable")]
    public function stable(): Json
    {
        return new Json(["stable" => true, "feature" => "production"]);
    }
} 