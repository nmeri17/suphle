<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Request\PayloadStorage;

	use Suphle\Response\Format\Markup;

	use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	class JsonNegotiator implements Middleware {

		private $activeRenderer;

		public function __construct (BaseRenderer $activeRenderer) {

			$this->activeRenderer = $activeRenderer;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			if ($this->activeRenderer instanceof Markup && $payloadStorage->acceptsJson())

				$this->activeRenderer->setWantsJson();

			return $requestHandler->handle($payloadStorage);
		}
	}
?>