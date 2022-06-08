<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	class HierarchialMiddleware1 implements Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$payloadStorage->mergePayload(["foo" => "bar"]);

			return $requestHandler->handle($payloadStorage);
		}
	}
?>