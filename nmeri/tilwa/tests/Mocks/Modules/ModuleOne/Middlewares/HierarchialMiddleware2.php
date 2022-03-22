<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Middleware\BaseMiddleware;

	class HierarchialMiddleware2 extends BaseMiddleware {

		public function process ($request, $requestHandler) {

			return $requestHandler->handle($request);
		}
	}
?>