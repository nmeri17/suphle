<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Coordinators\ServiceCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

/**
 * Simulates a v1 API coordinator with three endpoints.
 * V2 will extend this, inheriting the stable endpoints without
 * redefining them, and only overriding the one that changed.
 */
#[RoutePrefix("api/v1/products")]
class ProductsV1Coordinator extends ServiceCoordinator
{
    // Stable: inherited as-is in v2
    #[Route("/", HttpMethod::GET)]
    public function index(): Json
    {
        return new Json(['version' => 'v1', 'products' => ['Bolt', 'Nut']]);
    }

    // Stable: inherited as-is in v2
    #[Route("/{id}", HttpMethod::GET)]
    public function show(): Json
    {
        return new Json(['version' => 'v1', 'product' => 'Bolt']);
    }

    // Changed in v2: this method will be overridden
    #[Route("/", HttpMethod::POST)]
    public function store(): Json
    {
        return new Json(['version' => 'v1', 'created' => true, 'schema' => 'legacy']);
    }
}
