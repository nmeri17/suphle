<?php
	namespace Tilwa\Contracts\Routing;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Middleware\MiddlewareNexts;

	interface Middleware {

		// return response/renderer
		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler);
	}
?>