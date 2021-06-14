<?php

	namespace Tilwa\Auth;

	use Tilwa\App\Container;

	use Tilwa\Contracts\LoginRenderers;

	use Tilwa\Response\Format\AbstractRenderer;

	class LoginRequestHandler {

		private $rendererCollection, $container,

		$responseRenderer;

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

			$dependencies = $this->container->getMethodParameters("setDependencies", $this->responseRenderer);

			$dependencies["controllerClass"] = $this->responseRenderer->getController();

			$this->responseRenderer->setDependencies(...$dependencies);

			return $this;
		}

		private function executeRenderer ():void {

			$dependencies = $this->container->getMethodParameters(
				$this->responseRenderer->getHandler(),

				$this->responseRenderer->getController()
			);

			$this->responseRenderer->invokeActionHandler($dependencies);
		}
	}
?>