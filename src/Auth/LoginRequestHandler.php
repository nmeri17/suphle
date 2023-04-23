<?php
	namespace Suphle\Auth;

	use Suphle\Hydration\{Container, DecoratorHydrator, Structures\CallbackDetails};

	use Suphle\Contracts\{ Modules\HighLevelRequestHandler, Presentation\BaseRenderer};

	use Suphle\Contracts\Auth\{LoginFlowMediator, ModuleLoginHandler, LoginActions};

	use Suphle\Request\ValidatorManager;

	use Suphle\Services\Decorators\ValidationRules;

	use Suphle\Response\SetJsonValidationError;

	class LoginRequestHandler implements ModuleLoginHandler {

		use SetJsonValidationError;

		protected BaseRenderer $responseRenderer;

		protected LoginActions $loginService;

		public function __construct (
			protected readonly LoginFlowMediator $rendererCollection,

			protected readonly Container $container,

			protected readonly ValidatorManager $validatorManager,

			protected readonly DecoratorHydrator $decoratorHydrator,

			protected readonly CallbackDetails $callbackDetails
		) {

			$this->loginService = $rendererCollection->getLoginService();
		}

		public function isValidRequest ():bool {

			$attributesList = $this->callbackDetails->getMethodAttributes(

				$this->loginService::class, "successLogin",

				ValidationRules::class
			);

			$latestRules = end($attributesList)->newInstance();

			$this->validatorManager->setActionRules($latestRules->rules);
	
			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer (array $failureDetails):BaseRenderer {

			$renderer = $this->rendererCollection->failedRenderer(); // browser renderer uses Reload to connect to the get for us, while json returns a plain array as usual

			$renderer->forceArrayShape($failureDetails);

			return $renderer;
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