<?php
	namespace Tilwa\Middleware\Handlers;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Tilwa\Middleware\MiddlewareNexts;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Request\PayloadStorage;

	class FinalHandlerWrapper implements Middleware {

		private $rendererManager;

		public function __construct (RoutedRendererManager $rendererManager) {

			$this->rendererManager = $rendererManager;
		}

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$this->rendererManager->handleValidRequest($payloadStorage);

			$this->rendererManager->afterRender();

			return $this->rendererManager->responseRenderer();
		}
	}
?>