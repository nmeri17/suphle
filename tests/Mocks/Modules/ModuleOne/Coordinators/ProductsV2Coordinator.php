<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

use Suphle\Routing\Attributes\{Route, RoutePrefix, HttpMethod};
use Suphle\Response\Format\Json;

/**
 * Simulates a v2 API coordinator extending v1.
 *
 * - index() and show() are INHERITED from ProductsV1Coordinator — no redefinition needed.
 *   They will be registered by the scanner under the api/v2/products prefix automatically.
 *
 * - store() is OVERRIDDEN here because the v2 schema changed.
 *   The #[Route] attribute on the parent's store() is still visible through PHP Reflection
 *   (getMethods(IS_PUBLIC) returns inherited methods too), but since the child has its own
 *   store() method, only the child's definition is invoked during dispatch.
 *
 * This is the canonical Suphle pattern for API versioning without code duplication.
 */
#[RoutePrefix("api/v2/products")]
class ProductsV2Coordinator extends ProductsV1Coordinator
{
    // Inherited without override:
    // - index()  → GET /api/v2/products/
    // - show()   → GET /api/v2/products/{id}

    // Override: schema changed in v2
    #[Route("/", HttpMethod::POST)]
    public function store(): Json
    {
        return new Json(['version' => 'v2', 'created' => true, 'schema' => 'new']);
    }
}
