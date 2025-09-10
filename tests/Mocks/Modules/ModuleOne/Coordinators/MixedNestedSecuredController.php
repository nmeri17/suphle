<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class MixedNestedSecuredController extends ServiceCoordinator
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
