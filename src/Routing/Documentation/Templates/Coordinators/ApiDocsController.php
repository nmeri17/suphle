<?php
namespace _modules_shell\_module_name\Coordinators;

use Suphle\Services\BaseCoordinator;
use Suphle\Routing\Attributes\{Route, RoutePrefix};
use Suphle\Response\Format\{Json, Markup};
use _modules_shell\_module_name\Services\DocsDetails;

#[RoutePrefix("api-docs")]
class ApiDocsController extends BaseCoordinator
{
    public function __construct(
        protected readonly DocsDetails $docsDetailsService
    ) {
        //
    }

    #[Route("/")]
    public function showDocs(): Markup
    {        
        return new Markup('api-docs/index', []);
    }

    #[Route("/json")]
    public function getOpenApiJson(): Json
    {        
        return new Json($this->docsDetailsService->getJsonPayload());
    }
} 