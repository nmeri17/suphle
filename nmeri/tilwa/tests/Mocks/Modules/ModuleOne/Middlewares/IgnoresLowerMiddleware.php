<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\Format\Json;

	class IgnoresLowerMiddleware implements Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			return (new Json(""))->setRawResponse(["foo" => "bar"]);
		}
	}
?>