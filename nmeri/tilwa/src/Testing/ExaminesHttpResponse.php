<?php
	namespace Tilwa\Testing;

	use Illuminate\{Testing\TestResponse, Http\Response};

	use Tilwa\Response\Format\AbstractRenderer;

	trait ExaminesHttpResponse {

		protected function makeExaminable (AbstractRenderer $renderer):TestResponse {

			return TestResponse::fromBaseResponse(new Response(
				$renderer->getRawResponse(),

				$renderer->getStatusCode(),
				
				$renderer->getHeaders()
			));
		}
	}
?>