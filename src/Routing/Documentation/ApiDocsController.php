<?php

namespace Suphle\Routing\Documentation;

use Suphle\Coordinators\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\{Json, Markup};
use Suphle\Routing\OpenApiGeneratorService;

#[RoutePrefix("api-docs")]
class ApiDocsController extends BaseCoordinator
{
    public function __construct(
        protected readonly OpenApiGeneratorService $openApiService
    ) {
        //
    }

    #[Route("")]
    public function showDocs(): Markup
    {
        $openApiSpec = $this->openApiService->generateOpenApiSpec();
        $routes = $this->openApiService->getAllRoutes();
        
        return new Markup('api-docs.index', [
            'openApiSpec' => $openApiSpec,
            'routes' => $routes
        ]);
    }

    #[Route("json")]
    public function getOpenApiJson(): Json
    {
        $openApiSpec = $this->openApiService->generateOpenApiSpec();
        
        return new Json($openApiSpec);
    }
} 