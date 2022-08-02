<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\OutgoingRequests;

	use Suphle\IO\Http\BaseHttpRequest;

	use Psr\Http\Message\ResponseInterface;

	class VisitSegment extends BaseHttpRequest {

		protected function getHttpResponse ():ResponseInterface {

			$baseAddress = "http://localhost:8080"; // must match what's in rr.yaml

			return $this->requestClient->request(
			
				"get", $baseAddress ."/segment"/*, $options*/
			);
		}

		protected function convertToDomainObject (ResponseInterface $response) {

			return $response;
		}
	}
?>