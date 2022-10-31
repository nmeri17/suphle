<?php
	namespace Suphle\Services;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\{Requests\ValidationEvaluator, Presentation\BaseRenderer};

	use Suphle\Request\{ValidatorManager, RequestDetails};

	use Suphle\Routing\RouteManager;

	use Suphle\Exception\Explosives\Generic\NoCompatibleValidator;

	class CoodinatorManager implements ValidationEvaluator {

		private $controller, $container, $actionMethod,

		$handlerParameters, $validatorManager, $requestDetails,

		$router;

		function __construct( Container $container, ValidatorManager $validatorManager, RequestDetails $requestDetails, RouteManager $router) {

			$this->container = $container;

			$this->validatorManager = $validatorManager;

			$this->requestDetails = $requestDetails;

			$this->router = $router;
		}

		public function setDependencies (ServiceCoordinator $controller, string $actionMethod):self {
			
			$this->controller = $controller;

			$this->actionMethod = $actionMethod;

			return $this;
		}

		/**
		 * @throws NoCompatibleValidator
		*/
		public function bootController ():void {

			$this->updateValidatorMethod();

			$this->setHandlerParameters();
		}

		public function updateValidatorMethod ():void {

			$collectionName = $this->controller->validatorCollection ();

			$hasNoValidator = empty($collectionName) ||

			!method_exists($collectionName, $this->actionMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return;

				throw new NoCompatibleValidator(

					$this->controller::class, $this->actionMethod
				);
			}

			$this->validatorManager->setActionRules(

				call_user_func([

					$this->container->getClass($collectionName),

					$this->actionMethod
				])
			);
		}

		public function setHandlerParameters ():void {

			$this->handlerParameters = $this->container->getMethodParameters(

				$this->actionMethod, $this->controller::class
			);
		}

		public function getHandlerParameters():array {

			return $this->handlerParameters;
		}

		public function hasValidatorErrors ():bool {

			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}

		public function validationRenderer ():BaseRenderer {

			if (!$this->requestDetails->isApiRoute())

				return $this->router->getPreviousRenderer();

			return $this->router->getActiveRenderer();
		}
	}
?>