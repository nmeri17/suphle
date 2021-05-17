<?php

	namespace Tilwa\Controllers;

	use \InvalidArgumentException;

	use Tilwa\App\Container;

	use Tilwa\Contracts\{Orm, ControllerModel};

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Errors\CrowdedConstructor;

	class ControllerManager {

		private $controller, $container,

		$handlerParameters, $request, $actionModels;

		function __construct( Container $container) {

			$this->container = $container;
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

					$explicit->setIdentifier($this->request->$parameter);

					$this->handlerParameters[$parameter] = new ActionModelProxy($explicit);
				}
			}
		}

		public function bootController():self {

			$this->controller->setContainer($this->container);

			return $this;
		}

		public function assignActionRequest():self {

			foreach ($this->handlerParameters as $parameter) {
				
				if ($parameter instanceof BaseRequest)

					$this->request = $parameter;
			}
			return $this;
		}

		public function revertRequest(BaseRequest $previousRequest):self {

			$previousRequest->setValidationErrors( $this->request->validationErrors() );
			
			$this->request = $previousRequest;

			return $this;
		}

		public function getRequest():BaseRequest {
			
			return $this->request;
		}
		
		// this should go first before action argument instantiation
		public function updatePlaceholders():self {

			$pattern = "(?<![A-Z0-9])# negative lookbehind: given PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, refuse to match the h in the compound segment
			([a-z0-9]+)# pick placeholders"; // confirm this guy works with underscores, not slashes

			preg_match("/$pattern/x", $this->request->getPath(), $matches);

			$this->request->setPlaceholders($matches[0]);

			return $this;
		}
	}
?>