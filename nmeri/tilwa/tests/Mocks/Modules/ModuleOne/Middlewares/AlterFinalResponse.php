<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	class AlterFinalResponse extends BaseMiddleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$originalRenderer = $requestHandler->handle($payloadStorage);

			$originalRenderer->setRawResponse(array_merge(

				$originalRenderer->getRawResponse(), ["foo" => "baz"]
			));

			return $originalRenderer;
		}
	}
?>