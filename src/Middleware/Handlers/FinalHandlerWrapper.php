<?php
	namespace Suphle\Middleware\Handlers;

	use Suphle\Contracts\{Presentation\BaseRenderer, Routing\Middleware};

	use Suphle\Middleware\MiddlewareNexts;

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Request\PayloadStorage;

	class FinalHandlerWrapper implements Middleware {

		public function __construct(private readonly RoutedRendererManager $rendererManager)
  {
  }

		public function process (PayloadStorage $payloadStorage, ?MiddlewareNexts $requestHandler):BaseRenderer {

			$this->rendererManager->handleValidRequest($payloadStorage);

			$this->rendererManager->afterRender();

			return $this->rendererManager->responseRenderer();
		}
	}
?>