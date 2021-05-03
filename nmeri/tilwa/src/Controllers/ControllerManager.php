<?php

	namespace Tilwa\Controllers;

	use \InvalidArgumentException;

	use Tilwa\App\Container;

	use Tilwa\Contracts\Orm;

	use Tilwa\Http\Request\BaseRequest;

	use Tilwa\Errors\CrowdedConstructor;

	class ControllerManager {

		private $controller, $databaseAdapter, $container,

		$handlerParameters, $request, $actionModels;

		function __construct( Container $container, Orm $databaseAdapter) {

			$this->container = $container;

			$this->databaseAdapter = $databaseAdapter;
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

				return $this->databaseAdapter->isModel($parameter);
			}, ARRAY_FILTER_USE_KEY);

			return $this;
		}

		// mutates the underlying handler parameters
		public function hydrateModels(string $httpMethod):self {
			
			if ($httpMethod != "post") { // post has nothing to fetch/build
				$hydrator = "loadModelFor". ucfirst($httpMethod);

				foreach ($this->actionModels as $parameter => $model)

					$this->handlerParameters[$parameter] =call_user_func_array([$this, $hydrator], [$model::class, $this->request->$parameter]); // so, just drop this into that guy
			}
		}

		private function loadModelForGet(string $modelName, $modelId):object {
			
			return $this->databaseAdapter->findOne( $modelName, $modelId);
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