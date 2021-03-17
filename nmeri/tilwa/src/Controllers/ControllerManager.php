<?php

	namespace Tilwa\Controllers;

	use Tilwa\Errors\{IncompatibleService, CrowdedConstructor};

	use Tilwa\App\Container;

	use Tilwa\Contracts\Orm;

	use Tilwa\Http\Request\BaseRequest;

	class ControllerManager {

		private $controller;

		private $databaseAdapter;

		private $container;

		private $handlerParameters;

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

				throw new IncompatibleService( $controller->getInvalidService());

			if (!$controller->hasIsolatedConstructor())

				throw new CrowdedConstructor;
		}

		public function setHandlerParameters(string $actionMethod):void {

			$c = $this->controller;

			$this->handlerParameters = $this->container->getMethodParameters($actionMethod, $c::class);
		}

		public function getHandlerParameters():array {

			return $this->handlerParameters;
		}

		public function provideModelArguments(BaseRequest $request, string $httpMethod):void {

			$modelArguments = $this->findModelsInAction();

			if (!empty($modelArguments))

				$this->hydrateModels($request, $modelArguments, $httpMethod);
		}

		private function findModelsInAction():array {

			return array_filter($this->handlerParameters, function ($parameter) {

				return $this->databaseAdapter->isModel($argument);
			}, ARRAY_FILTER_USE_KEY);
		}

		// mutates the underlying handler parameters
		private function hydrateModels(BaseRequest $request, array $modelArguments, string $httpMethod):void {
			
			if ($httpMethod != "post") {// nothing to fetch/build. user is creating a new resource

				$modelFactory = "loadModelFor". ucfirst($httpMethod);

				foreach ($modelArguments as $parameter => $model)

					$this->handlerParameters[ $parameter] = $this->$modelFactory(
						$model::class, $request->$parameter, $parameter
					);
			}
		}

		private function loadModelForGet(string $modelName, $modelId):object {
			
			return $this->databaseAdapter->findOne( $modelName, $modelId);
		}

		private function loadModelForPut(string $modelName, $modelId, string $columnName):object {
			
			return $this->databaseAdapter->builderWhere( $modelName, $modelId, $columnName);
		}

		private function loadModelForDelete(string $modelName, $modelId, string $columnName):object {
			
			return $this->loadModelForPut($modelName, $modelId, $columnName);
		}

		public function bootController():void {

			$this->controller->setContainer($this->container)

			->registerFactories();
		}
	}
?>