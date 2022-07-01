<?php
	namespace Tilwa\Services;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Requests\ValidationEvaluator;

	use Tilwa\Request\{ValidatorManager, RequestDetails};

	use Tilwa\Exception\Explosives\Generic\NoCompatibleValidator;

	class CoodinatorManager implements ValidationEvaluator {

		private $controller, $container, $actionMethod,

		$handlerParameters, $validatorManager, $requestDetails;

		function __construct( Container $container, ValidatorManager $validatorManager, RequestDetails $requestDetails) {

			$this->container = $container;

			$this->validatorManager = $validatorManager;

			$this->requestDetails = $requestDetails;
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

					get_class($this->controller), $this->actionMethod
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

				$this->actionMethod, get_class($this->controller)
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
	}
?>