<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Response\ResponseManager;

	use Tilwa\Request\PayloadStorage;

	class FinalHandlerWrapper extends BaseMiddleware {

		private $manager;

		public function __construct (ResponseManager $manager) {

			$this->manager = $manager;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			$this->manager->handleValidRequest($payloadStorage);

			$this->manager->afterRender();

			return $this->manager->getResponse();
		}
	}
?>