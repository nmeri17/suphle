<?php
	namespace Tilwa\Services;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{Requests\ValidationEvaluator, Database\OrmDialect};

	use Tilwa\Request\ValidatorManager;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Services\Structures\{ModelfulPayload, ModellessPayload};

	use Tilwa\Exception\Explosives\Generic\NoCompatibleValidator;

	class CoodinatorManager implements ValidationEvaluator {

		private $controller, $container, $actionMethod,

		$handlerParameters, $validatorManager, $requestDetails,

		$actionInjectables = [

			ModelfulPayload::class, ModellessPayload::class
		];

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

			$hasNoValidator = empty($collectionName) || !method_exists($collectionName, $this->actionMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return;

				throw new NoCompatibleValidator;
			}

			$this->validatorManager->setActionRules(

				call_user_func([$this->container->getClass($collectionName), $this->actionMethod])
			);
		}

		private function setHandlerParameters ():void {

			$parameters = $this->container->getMethodParameters($this->actionMethod, get_class($this->controller));

			$correctParameters = $this->validActionDependencies($parameters);

			$this->prepareActionModels($correctParameters);

			$this->handlerParameters = $correctParameters;
		}

		public function validActionDependencies (array $argumentList):array {

			$newList = [];

			foreach ($argumentList as $argument => $dependency) { // silently fail

				foreach ($this->actionInjectables as $validType)

					if ($dependency instanceof $validType) {

						$newList[$argument] = $dependency;

						break;
					}
			}

			return $newList;
		}

		public function prepareActionModels (array $argumentList):void {

			$orm = null;

			foreach ($argumentList as $dependency) {

				if (!($dependency instanceof ModelfulPayload))

					continue;

				if (is_null($orm))

					$orm = $this->container->getClass(OrmDialect::class);

				$dependency->setDependencies($orm);
			}
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