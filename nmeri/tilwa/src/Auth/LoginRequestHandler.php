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

		public function processLoginRequest ():void {

			$renderer = $this->responseRenderer;

			$renderer->setControllingClass($this->loginService);
			
			$renderer->hydrateDependencies($this->container);

			$this->executeRenderer();
		}

		public function setResponseRenderer ():ModuleLoginHandler {

			if ($this->loginService->compareCredentials())

				$this->responseRenderer = $this->rendererCollection->successRenderer();

			else $this->responseRenderer = $this->rendererCollection->failedRenderer();

			return $this;
		}

		private function executeRenderer ():void {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters(
				$renderer->getHandler(),

				get_class($renderer->getController())
			);

			$renderer->invokeActionHandler($dependencies);
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->responseRenderer;
		}
	}
?>