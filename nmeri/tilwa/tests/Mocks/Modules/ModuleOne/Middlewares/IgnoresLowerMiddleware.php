<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	class IgnoresLowerMiddleware extends BaseMiddleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return (new Json(""))->setRawResponse(["foo" => "bar"]);
		}
	}
?>