<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\Presentation\{MirrorableRenderer, BaseRenderer};

	use Suphle\Contracts\Routing\Middleware;

	class JsonNegotiator implements Middleware {

		private $activeRenderer;

		public function __construct (BaseRenderer $activeRenderer) {

			$this->activeRenderer = $activeRenderer;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			if ($this->activeRenderer instanceof MirrorableRenderer && $payloadStorage->acceptsJson())

				$this->activeRenderer->setWantsJson();

			return $requestHandler->handle($payloadStorage);
		}
	}
?>