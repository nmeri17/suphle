<?php
	namespace Tilwa\Testing\Proxies;

	use Tilwa\Contracts\{Presentation\BaseRenderer, IO\Session};

	use Tilwa\IO\Session\InMemorySession;

	use Tilwa\Testing\Proxies\Extensions\TestResponseBridge;

	use Illuminate\Http\Response;

	trait ExaminesHttpResponse {

		protected function makeExaminable (BaseRenderer $renderer):TestResponseBridge {

			$response = new Response(

				$renderer->getRawResponse(), $renderer->getStatusCode(),
				
				$renderer->getHeaders()
			);

			return new TestResponseBridge($response, $this->sessionClient);
		}

		/**
		 * This should be called before request execution i.e. before [makeExaminable] i.e. on setUp
		*/
		protected function massProvideSession ():void {

			$this->massProvide([

				Session::class => $this->sessionClient = new InMemorySession
			]);
		}
	}
?>