<?php
	namespace Tilwa\Auth;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{ Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Tilwa\Contracts\Auth\{LoginRenderers, ModuleLoginHandler};

	use Tilwa\Request\ValidatorManager;

	class LoginRequestHandler implements ModuleLoginHandler {

		private $rendererCollection, $container, $responseRenderer,

		$validatorManager;

		public function __construct (LoginRenderers $collection, Container $container, ValidatorManager $validatorManager) {

			$this->rendererCollection = $collection;

			$this->container = $container;

			$this->validatorManager = $validatorManager;

			$this->loginService = $collection->getLoginService();
		}

		public function isValidRequest ():bool {

			$this->validatorManager->setActionRules($this->loginService->successRules());
	
			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function getResponse ():string {

			$this->setResponseRenderer();

			$renderer = $this->responseRenderer;

			$renderer->setControllingClass($this->rendererCollection->getLoginService());
			
			$renderer->hydrateDependencies($this->container);

			$this->executeRenderer();

			return $renderer->render();
		}

		private function setResponseRenderer ():void {

			if ($this->loginService->compareCredentials())

				$this->responseRenderer = $this->rendererCollection->successRenderer();

			else $this->responseRenderer = $this->rendererCollection->failedRenderer();
		}

		private function executeRenderer ():void {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters(
				$renderer->getHandler(),

				$renderer->getController()
			);

			$renderer->invokeActionHandler($dependencies);
		}

		public function handlingRenderer ():BaseRenderer {

			return $this->responseRenderer;
		}
	}
?>