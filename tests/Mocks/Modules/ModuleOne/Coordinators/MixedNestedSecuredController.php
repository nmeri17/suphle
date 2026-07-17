<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod, RoutePrefix};
use Suphle\Response\Format\Json;

#[RoutePrefix("/nested")]
class MixedNestedSecuredController extends BaseCoordinator
{
    #[Route("unlink")]
    public function handleUnlinked(): Json
    {
        return new Json([]);
    }

    #[Route("retain-auth")]
    public function handleRetained(): Json
    {
        return new Json([]);
    }
}
