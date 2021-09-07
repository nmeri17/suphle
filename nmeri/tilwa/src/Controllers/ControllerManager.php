<?php

	namespace Tilwa\Controllers;

	use \InvalidArgumentException;

	use Tilwa\App\Container;

	use Tilwa\Contracts\{Orm, ControllerModel};

	use Tilwa\Request\ValidatorManager;

	use Tilwa\Errors\CrowdedConstructor;

	use Tilwa\Routing\PathPlaceholders;

	class ControllerManager {

		private $controller, $container, $placeholderStorage,

		$handlerParameters, $actionModels, $validatorManager,

		$actionMethod;

		function __construct( Container $container, PathPlaceholders $placeholderStorage, ValidatorManager $validatorManager) {

			$this->container = $container;

			$this->placeholderStorage = $placeholderStorage;

			$this->validatorManager = $validatorManager;
		}

		public function setController(Executable $controller):void {
			
			$this->controller = $controller;
		}

		public function validateController (array $moduleDependencies):void {

			$controller = $this->controller;
			
			if (!$controller->hasValidServices ($moduleDependencies ))

				throw new InvalidArgumentException ("Incompatible Service: ". $controller->getInvalidService());

			if (!$controller->hasIsolatedConstructor())

				throw new CrowdedConstructor;
		}

		public function setHandlerParameters():self {

			$this->handlerParameters = $this->container->getMethodParameters($this->actionMethod, get_class($this->controller));

			return $this;
		}

		public function getHandlerParameters():array {

			return $this->handlerParameters;
		}

		public function assignModelsInAction():self {

			$this->actionModels = array_filter($this->handlerParameters, function ($parameter) {

				return $parameter instanceof ControllerModel;
			});

			return $this;
		}

		// mutates the underlying handler parameters
		public function hydrateModels(string $httpMethod):self {
			
			if ($httpMethod != "post") { // post has nothing to fetch/build

				foreach ($this->actionModels as $parameter => $modelWrapper) {

					$explicit = $this->container->getClass($modelWrapper);

					$explicit->setIdentifier($this->placeholderStorage->getSegmentValue($parameter));

					$this->handlerParameters[$parameter] = new ActionModelProxy($explicit);
				}
			}
		}

		public function bootController (string $actionMethod):self {

			$this->actionMethod = $actionMethod;

			$this->updateValidatorMethod();

			$this->controller->setContainer($this->container);

			return $this;
		}

		private function updateValidatorMethod ():void {

			$actionMethod = $this->actionMethod;

			$collectionName = $this->controller->validatorCollection ();

			if (empty($collectionName) || !method_exists($collectionName, $actionMethod)) return;

			$this->validatorManager->setActionRules(call_user_func([$collectionName, $actionMethod]))
		}

		public function isValidatedRequest ():bool {

			return $this->validatorManager->isValidated();
		}

		public function getValidatorErrors ():array {

			return $this->validatorManager->validationErrors();
		}
	}
?>