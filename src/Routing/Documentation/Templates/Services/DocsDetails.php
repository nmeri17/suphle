<?php
namespace _modules_shell\_module_name\Services;

use Suphle\Services\Decorators\DomainService;
use Suphle\Routing\OpenApiGeneratorService;
use Suphle\Request\PayloadStorage;

#[DomainService]
class DocsDetails {
	public function __construct (
		private readonly OpenApiGeneratorService $openApiService,

		private readonly PayloadStorage $payloadStorage
	) {}
	
	public function getJsonPayload ():array {

    	$uri = $this->payloadStorage->getUri();
        
        return $this->openApiService->generateOpenApiSpec($uri->getScheme() . '://' . $uri->getHost());
	}
}