<?php
	namespace Suphle\Auth;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\{ Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Suphle\Contracts\Auth\{LoginFlowMediator, ModuleLoginHandler};

	use Suphle\Request\ValidatorManager;

	use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;

	class LoginRequestHandler implements ModuleLoginHandler {

		private $rendererCollection, $container, $responseRenderer,

		$validatorManager, $variableDecorator, $loginService;

		public function __construct (
			LoginFlowMediator $collection, Container $container,

			ValidatorManager $validatorManager, VariableDependenciesHandler $variableDecorator) {

			$this->rendererCollection = $collection;

			$this->container = $container;

			$this->validatorManager = $validatorManager;

			$this->loginService = $collection->getLoginService();

			$this->variableDecorator = $variableDecorator;
		}

		public function isValidRequest ():bool {

			$this->validatorManager->setActionRules($this->loginService->successRules());
	
			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer ():BaseRenderer {

			return $this->rendererCollection->failedRenderer(); // browser renderer uses Reload to connect to the get for us, while json returns a plain array as usual
		}

		public function processLoginRequest ():void {

			$renderer = $this->responseRenderer;

			$renderer->setControllingClass($this->loginService);

			$this->executeRenderer();
		}

		public function setResponseRenderer ():ModuleLoginHandler {

			if ($this->loginService->compareCredentials())

				$renderer = $this->rendererCollection->successRenderer();

			else $renderer = $this->rendererCollection->failedRenderer();

			$this->responseRenderer = $this->variableDecorator

			->examineInstance($renderer, get_class());

			return $this;
		}

		private function executeRenderer ():void {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters(
				$renderer->getHandler(),

				$renderer->getController()::class
			);

			$renderer->invokeActionHandler($dependencies);
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->responseRenderer;
		}
	}
?>