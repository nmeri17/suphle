<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Bootstrap;

	use Tilwa\Contracts\Orm;

	use \Generator;

	class RouteManager {

		private $module;

		private $activeRoute;

		private $payload;

		private $handlerParameters;

		private $databaseAdapter;

		private $requestIndexInParameters;

		private $modelIndexesInParameters;

		private $pathPlaceholders;

		private $incomingPath;

		private $httpMethod;

		function __construct(Bootstrap $module, string $incomingPath, string $httpMethod ) {

			$this->module = $module;

			$this->databaseAdapter = $module->getClass(Orm::class);

			$this->incomingPath = $incomingPath;

			$this->httpMethod = $httpMethod;
		}

		/**
		 * @param {requestPath}: does not support query urls
		 *
		 **/
		public function findRoute () {

			$hit = null;

			foreach ($this->loadRoutesFromClass() as $route) {
				
				if ($this->routeCompare($route)) {

					$hit = $route;

					break;
				}
			}
			if (!is_null($hit))

				$this->updateRequestParameters($hit->setPath($requestPath));

			return $hit;
		}

		// if any of the methods returns a class string to its caller (the app) instead of a route (or updates the `prefix` context property), we toss it back into this method
		public function loadRoutesFromClass():Generator {
			// first check if `this->isApiRoute()` before deciding on whether to load this or the API route stack
			$collection = $this->module->getClass($this->module->getAppMainRoutes())

			->getLeaves(); // array of strings

			// $collection-> # then do your generator ish here
		}
		
		// request method vs route http method is only done on the final path. empty incoming path should check for presence of an index method
		// will likely work hand in hand with the guy above
		public function routeCompare(Route $route):bool {

			// reset `pathPlaceholders` on each parent/root route. populate it subsequently for leaves under it

			// work with this->incomingPath
		}
		
		public function updateRequestParameters():void {}

		public function setPrevious(Route $route ):static {

			$_SESSION['prev_route'] = $route;

			return $this;
		}

		public function getPrevious ():Route {

			return $_SESSION['prev_route'];
		}

		public function getActiveRoute ():Route {

			return $this->activeRoute;
		}

		public function setActiveRoute (Route $route):static {

			$this->activeRoute = $route;

			return $this;
		}

		public function savePayload():static {
			
			$payloadAnchor = 'tilwa_request';

			$this->payload = array_filter($_GET + $_POST, function ( $key) use ($payloadAnchor) {

				return $key !== $payloadAnchor;
			}, ARRAY_FILTER_USE_KEY);

			unset($_GET[$payloadAnchor], $_POST[$payloadAnchor]);

			return $this;
		}

		/**
		* @return previous Route
		*/
		public function mergeWithPrevious(BaseRequest $request):Route {
			
			$route = $this->getPrevious();

			$route->getRequest()

			->setValidationErrors( $request->validationErrors() );

			return $route;
		}

		public function prepareArguments():array {

			$route = $this->activeRoute;

			$this->handlerParameters = $this->module->getMethodParameters($route->handler, $route->getController());

			$this->warmParameters();

			if (!is_null($this->requestIndexInParameters))

				$this->updateRequestPayload();

			if (!empty($this->modelIndexesInParameters))

				$this->hydrateModels();

			return $this->handlerParameters;
		}

		private function warmParameters():void {
			
			foreach ($this->handlerParameters as $idx => $arg) {
				
				if ($arg instanceof BaseRequest)
				
					$this->requestIndexInParameters = $idx;

				elseif ( $this->databaseAdapter->isModel($arg))

					$this->modelIndexesInParameters[] = $idx;
			}
		}

		private function updateRequestPayload():void {

			$request = $this->handlerParameters[$this->requestIndexInParameters]->setPayload($this->payload);

			$this->activeRoute->setRequest ($request);
		}

		/*
		* @description: assumes ordering of the arguments on the action handler matches the one on url pattern

			handler (BaseRequest, Model1, Random, Model2)
			path/2/action/3 = [2,3]
		*/
		private function hydrateModels():void {

			$pathPlaceholders = array_values($this->activeRoute->placeholderMap);
			
			foreach ($this->modelIndexesInParameters as $index => $modelIndex) {

				$defaultModel = $this->handlerParameters[ $modelIndex];

				$this->handlerParameters[ $modelIndex] = $this->databaseAdapter
				->findOne(
					$defaultModel::class, $pathPlaceholders[$index]
				);
			}
		}

		public function isApiRoute ():bool {

			return preg_match("/^" . $this->module->apiPrefix() . "/", $this->incomingPath);
		}
	}
?>