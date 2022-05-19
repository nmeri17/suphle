<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	class HierarchialMiddleware1 extends BaseMiddleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$payloadStorage->mergePayload(["foo" => "bar"]);

			return $requestHandler->handle($payloadStorage);
		}
	}
?>