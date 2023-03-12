<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Middleware\{MiddlewareNexts, CollectibleMiddlewareHandler};

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\Presentation\{MirrorableRenderer, BaseRenderer};

	/**
	 * This seems like a far simpler alternative to route mirroring. But middleware only runs after routing. Moreover, this doesn't offer the flexibility of overriding mirrored routes
	*/
	class JsonNegotiator extends CollectibleMiddlewareHandler {

		public function __construct(protected readonly BaseRenderer $activeRenderer) {
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			if ($this->activeRenderer instanceof MirrorableRenderer && $payloadStorage->acceptsJson())

				$this->activeRenderer->setWantsJson();

			return $requestHandler->handle($payloadStorage);
		}
	}
?>