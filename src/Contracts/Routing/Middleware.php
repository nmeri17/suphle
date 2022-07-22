<?php
	namespace Suphle\Contracts\Routing;

	use Suphle\Contracts\Presentation\BaseRenderer;

	use Suphle\Request\PayloadStorage;

	use Suphle\Middleware\MiddlewareNexts;

	interface Middleware {

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer;
	}
?>