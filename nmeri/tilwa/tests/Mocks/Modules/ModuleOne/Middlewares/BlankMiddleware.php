<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	class BlankMiddleware implements Middleware {

		public function process (PayloadStorage $request, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return $requestHandler->handle($request);
		}
	}
?>