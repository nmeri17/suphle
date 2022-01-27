<?php
	namespace Tilwa\Controllers;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{Requests\ValidationEvaluator, Database\Orm, Services\Models\ActionTransform};

	use Tilwa\Request\ValidatorManager;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Controllers\Structures\ControllerModel;

	use Tilwa\Exception\Explosives\Generic\NoCompatibleValidator;

	class ControllerManager implements ValidationEvaluator {

		private $controller, $container,

		$handlerParameters, $validatorManager, $requestDetails,

		$actionInjectables = [

			ControllerModel::class, ActionTransform::class
		];

		function __construct( Container $container, ValidatorManager $validatorManager, RequestDetails $requestDetails) {

			$this->container = $container;

			$this->validatorManager = $validatorManager;

			$this->requestDetails = $requestDetails;
		}

		public function setController(ServiceCoordinator $controller):void {
			
			$this->controller = $controller;
		}

		/**
		 * @throws NoCompatibleValidator
		*/
		public function bootController (string $actionMethod):void {

			$this->updateValidatorMethod($actionMethod);

			$this->setHandlerParameters($actionMethod);
		}

		private function updateValidatorMethod (string $actionMethod):void {

			$collectionName = $this->controller->validatorCollection ();

			$hasNoValidator = empty($collectionName) || !method_exists($collectionName, $actionMethod);

			if ($hasNoValidator) {

				if ($this->requestDetails->isGetRequest()) return;

				throw new NoCompatibleValidator;
			}

			$this->validatorManager->setActionRules(

				$this->container->getClass($collectionName)->$actionMethod()
			);
		}

		private function setHandlerParameters (string $actionMethod):void {

			$parameters = $this->container->getMethodParameters($actionMethod, get_class($this->controller));

			$correctParameters = $this->validActionDependencies($parameters);

			$this->prepareActionModels($correctParameters);

			$this->handlerParameters = $correctParameters;
		}

		private function validActionDependencies (array $argumentList):array {

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

		private function prepareActionModels (array $argumentList):void {

			$orm = null;

			foreach ($argumentList as $dependency) {

				if (!($dependency instanceof ControllerModel))

					continue;

				if (is_null($orm))

					$orm = $this->container->getClass(Orm::class);

				$dependency->setDependencies($orm);
			}
		}

		public function getHandlerParameters():array {

			return $this->handlerParameters;
		}

		public function isValidatedRequest ():bool {

			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}
	}
?>