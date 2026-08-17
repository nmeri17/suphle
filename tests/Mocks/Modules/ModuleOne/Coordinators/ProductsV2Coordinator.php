<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

#[RoutePrefix("/products", mirrorPrefix: "api/v2")]
class ProductsV2Coordinator extends ProductsV1Coordinator
{
    // Inherited without override:
    // - index()  → GET /api/v2/products/
    // - show()   → GET /api/v2/products/{id}

    // Override: schema changed in v2
    #[Route("/", HttpMethod::POST)]
    #[ValidationRules([
        "field" => "required"
    ])]
    public function store(): Json
    {
        return new Json(['version' => 'v2', 'created' => true, 'schema' => 'new']);
    }
}
