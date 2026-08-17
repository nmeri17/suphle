<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Services\{BaseCoordinator, Decorators\ValidationRules};
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

#[RoutePrefix("/products", mirrorPrefix: "api/v1")]
class ProductsV1Coordinator extends BaseCoordinator
{
    #[Route("/")]
    public function index(): Json
    {
        return new Json(['version' => 'v1', 'products' => ['Bolt', 'Nut']]);
    }

    #[Route("/{id}")]
    public function show(): Json
    {
        return new Json(['version' => 'v1', 'product' => 'Bolt']);
    }

    #[Route("/", HttpMethod::POST)]
    #[ValidationRules([])]
    public function store(): Json
    {
        return new Json(['version' => 'v1', 'created' => true, 'schema' => 'legacy']);
    }
}
