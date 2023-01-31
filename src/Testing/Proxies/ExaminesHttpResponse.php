<?php
	namespace Suphle\Testing\Proxies;

	use Suphle\Contracts\{Presentation\BaseRenderer, IO\Session};

	use Suphle\Adapters\Session\InMemorySession;

	use Suphle\Testing\Proxies\Extensions\TestResponseBridge;

	use Illuminate\Http\Response;

	trait ExaminesHttpResponse {

		protected function makeExaminable (BaseRenderer $renderer):TestResponseBridge {

			$response = new Response(

				$renderer->render(), // rendering straight away instead of giving them the raw response since they have no way of parsing possible html from our templates

				$renderer->getStatusCode(), $renderer->getHeaders()
			);

			return new TestResponseBridge(

				$response, $this->getContainer()->getClass(Session::class)
			);
		}
	}
?>