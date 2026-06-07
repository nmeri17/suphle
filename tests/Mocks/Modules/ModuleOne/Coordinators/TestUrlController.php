<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, HttpMethod};
use Suphle\Response\Format\Json;

class TestUrlController extends BaseCoordinator
{
    #[Route("test-url", method: HttpMethod::GET)]
    public function sameUrl(): Json
    {
        return new Json([]);
    }

    #[Route("test-url-2", method: HttpMethod::GET)]
    public function sameUrl2(): Json
    {
        return new Json([]);
    }

    #[Route("test-url-3", method: HttpMethod::GET)]
    public function sameUrl3(): Json
    {
        return new Json([]);
    }

    #[Route("dynamic/{id}", method: HttpMethod::GET)]
    public function dynamicSegment(): Json
    {
        return new Json([]);
    }
} 