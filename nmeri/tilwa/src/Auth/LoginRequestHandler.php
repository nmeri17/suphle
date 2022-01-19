<?php
	namespace Tilwa\Auth;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{Auth\LoginRenderers, App\HighLevelRequestHandler, Request\ValidationEvaluator};

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Request\ValidatorManager;

	class LoginRequestHandler implements HighLevelRequestHandler, ValidationEvaluator {

		private $rendererCollection, $container, $responseRenderer,

		$validatorManager;

		public function __construct (LoginRenderers $collection, Container $container, ValidatorManager $validatorManager) {

			$this->rendererCollection = $collection;

			$this->container = $container;

			$this->validatorManager = $validatorManager;
		}

		public function setAuthService ():void {

			$this->loginService = $this->rendererCollection->getLoginService();
		}

		public function isValidRequest ():bool {

			$this->validatorManager->setActionRules($this->getLoginRules());
	
			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		private function getLoginRules ():array {

			$validatorName = $this->loginService->validatorCollection();

			return $this->container->getClass($validatorName)->successRules();
		}

		public function getResponse ():string {

			$this->setResponseRenderer();

			$this->bootRenderer()->executeRenderer();

			return $this->responseRenderer->render();
		}

		private function setResponseRenderer ():void {

			if ($this->loginService->compareCredentials())

				$this->responseRenderer = $this->rendererCollection->successRenderer();

			else $this->responseRenderer = $this->rendererCollection->failedRenderer();
		}

		private function bootRenderer ():self {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters("setDependencies", $renderer);

			$dependencies["controllerClass"] = $renderer->getController();

			$renderer->setDependencies(...array_values($dependencies));

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

		public function handlingRenderer ():AbstractRenderer {

			return $this->responseRenderer;
		}
	}
?>