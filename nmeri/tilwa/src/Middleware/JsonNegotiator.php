<?php
	namespace Tilwa\Middleware;

	use Tilwa\Request\PayloadStorage;

	use Tilwa\Response\Format\Markup;

	use Tilwa\Contracts\Presentation\BaseRenderer;

	class JsonNegotiator extends BaseMiddleware {

		private $renderer;

		public function __construct (BaseRenderer $renderer) {

			$this->renderer = $renderer;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			if ($this->renderer instanceof Markup && $payloadStorage->acceptsJson())

				$this->renderer->setWantsJson();
		}
	}
?>