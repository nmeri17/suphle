<?php

	namespace Tilwa\Controllers;

	use \InvalidArgumentException;

	use Tilwa\App\Container;

	use Tilwa\Contracts\{Orm, ControllerModel};

	use Tilwa\Request\ValidatorDTO;

	use Tilwa\Errors\CrowdedConstructor;

	use Tilwa\Routing\PathPlaceholders;

	class ControllerManager {

		private $controller, $container, $placeholderStorage,

		$handlerParameters, $actionModels;

		function __construct( Container $container, PathPlaceholders $placeholderStorage) {

			$this->container = $container;

			$this->placeholderStorage = $placeholderStorage;
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

		public function setHandlerParameters(string $actionMethod):self {

			$this->handlerParameters = $this->container->getMethodParameters($actionMethod, get_class($this->controller));

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

		public function bootController():self {

			$this->controller->setContainer($this->container);

			return $this;
		}

		public function revertRequest(ValidatorDTO $previousRequest):self {

			$previousRequest->setValidationErrors( $this->request->validationErrors() ); 
			
			$this->request = $previousRequest;

			return $this;
		}
	}
?>