<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	class BlankMiddleware4 extends BaseMiddleware {

		public function process (PayloadStorage $request, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return $requestHandler->handle($request);
		}
	}
?>