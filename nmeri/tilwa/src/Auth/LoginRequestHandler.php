<?php

	namespace Tilwa\Auth;

	use Tilwa\App\Container;

	use Tilwa\Contracts\LoginRenderers;

	use Tilwa\Response\Format\AbstractRenderer;

	class LoginRequestHandler {

		private $rendererCollection, $container, $responseRenderer;

		public function __construct (LoginRenderers $renderer, Container $container) {

			$this->rendererCollection = $renderer;

			$this->container = $container;
		}

		public function getResponse ():string {

			$loginService = $this->rendererCollection->getLoginService();

			$status = $loginService->compareCredentials();

			$this->setResponseRenderer($status);

			$this->bootRenderer()->executeRenderer();

			return $this->responseRenderer->render();
		}

		private function setResponseRenderer (bool $status):void {

			if ($status)

				$this->responseRenderer = $this->rendererCollection->successRenderer();

			else $this->responseRenderer = $this->rendererCollection->failedRenderer();
		}

		private function bootRenderer ():self {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters("setDependencies", $renderer);

			$dependencies["controllerClass"] = $renderer->getController();

			$renderer->setDependencies(...$dependencies);

			return $this;
		}

		private function executeRenderer ():void {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters(
				$renderer->getHandler(),

				$renderer->getController()
			);

			$renderer->invokeActionHandler($dependencies);
		}
	}
?>