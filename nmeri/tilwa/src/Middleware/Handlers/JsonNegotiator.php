<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\Format\Markup;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	class JsonNegotiator extends BaseMiddleware {

		private $activeRenderer;

		public function __construct (BaseRenderer $activeRenderer) {

			$this->activeRenderer = $activeRenderer;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			if ($this->activeRenderer instanceof Markup && $payloadStorage->acceptsJson())

				$this->activeRenderer->setWantsJson();

			return $requestHandler->handle($payloadStorage);
		}
	}
?>