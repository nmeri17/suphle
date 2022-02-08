<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	class HierarchialMiddleware1 extends BaseMiddleware {

		public function process ($payloadStorage, $requestHandler) {

			$payloadStorage->mergePayload(["foo" => "bar"]);

			return $requestHandler->handle($payloadStorage);
		}
	}
?>