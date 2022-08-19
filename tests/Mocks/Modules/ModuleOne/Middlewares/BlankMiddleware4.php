<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Request\PayloadStorage;

	class BlankMiddleware4 implements Middleware {

		public function process (PayloadStorage $request, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return $requestHandler->handle($request);
		}
	}
?>