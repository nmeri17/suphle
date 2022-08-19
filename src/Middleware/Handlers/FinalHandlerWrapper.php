<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Request\PayloadStorage;

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