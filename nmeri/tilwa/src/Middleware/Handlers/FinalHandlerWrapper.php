<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Middleware\{BaseMiddleware, MiddlewareNexts};

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Request\PayloadStorage;

	class FinalHandlerWrapper extends BaseMiddleware {

		private $rendererManager;

		public function __construct (RoutedRendererManager $rendererManager) {

			$this->rendererManager = $rendererManager;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler) {

			$this->rendererManager->handleValidRequest($payloadStorage);

			$this->rendererManager->afterRender();

			return $this->rendererManager->getResponse();
		}
	}
?>