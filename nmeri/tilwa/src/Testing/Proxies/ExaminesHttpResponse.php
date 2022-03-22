<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Illuminate\{Testing\TestResponse, Http\Response};

	trait ExaminesHttpResponse {

		protected function makeExaminable (BaseRenderer $renderer):TestResponse {

			return TestResponse::fromBaseResponse(new Response(
				$renderer->getRawResponse(),

				$renderer->getStatusCode(),
				
				$renderer->getHeaders()
			));
		}
	}
?>