<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\Middleware;

	class BlankMiddleware implements Middleware {

		public function process ($request, $requestHandler) {

			return $requestHandler($request);
		}
	}
?>