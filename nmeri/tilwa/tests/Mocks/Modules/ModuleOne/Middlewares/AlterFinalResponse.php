<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	class AlterFinalResponse implements Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$originalRenderer = $requestHandler->handle($payloadStorage);

			$originalRenderer->setRawResponse(array_merge(

				$originalRenderer->getRawResponse(), ["foo" => "baz"]
			));

			return $originalRenderer;
		}
	}
?>