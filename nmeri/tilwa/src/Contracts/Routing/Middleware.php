<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Middleware\MiddlewareNexts;

	interface Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer;
	}
?>