<?php
	namespace Suphle\Auth;

	use Suphle\Hydration\{Container, DecoratorHydrator};

	use Suphle\Contracts\{ Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Suphle\Contracts\Auth\{LoginFlowMediator, ModuleLoginHandler, LoginActions};

	use Suphle\Request\ValidatorManager;

	class LoginRequestHandler implements ModuleLoginHandler {

		private BaseRenderer $responseRenderer;

		private LoginActions $loginService;

		public function __construct (
			private readonly LoginFlowMediator $rendererCollection,

			private readonly Container $container,

			private readonly ValidatorManager $validatorManager,

			private readonly DecoratorHydrator $decoratorHydrator
		) {

			$this->loginService = $rendererCollection->getLoginService();
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

			$renderer->setCoordinatorClass($this->loginService);

			$this->executeRenderer();
		}

		public function setResponseRenderer ():ModuleLoginHandler {

			if ($this->loginService->compareCredentials())

				$renderer = $this->rendererCollection->successRenderer();

			else $renderer = $this->rendererCollection->failedRenderer();

			$this->responseRenderer = $this->decoratorHydrator

			->scopeInjecting($renderer, self::class);

			return $this;
		}

		private function executeRenderer ():void {

			$renderer = $this->responseRenderer;

			$dependencies = $this->container->getMethodParameters(
				$renderer->getHandler(),

				$renderer->getCoordinator()::class
			);

			$renderer->invokeActionHandler($dependencies);
		}

		public function handlingRenderer ():?BaseRenderer {

			return $this->responseRenderer;
		}
	}
?>