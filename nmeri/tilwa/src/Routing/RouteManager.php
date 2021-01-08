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

		private $incomingPath;

		private $httpMethod;

		function __construct(Bootstrap $module, string $incomingPath, string $httpMethod ) {

			$this->module = $module;

			$this->databaseAdapter = $module->getClass(Orm::class);

			$this->incomingPath = $incomingPath;

			$this->httpMethod = $httpMethod;
		}

		public function findRoute ():Route {

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$this->updateRequestParameters($hit->setPath($this->incomingPath));

					return $hit;
				}
			}
		}

		// if any of the methods returns a class string to its caller (the app) instead of a route (or updates the `prefix` context property), we toss it back into this method
		public function loadPatterns(object $instance):Generator {

			if ($instance->_passover())
			
				foreach ($instance->getPatterns() as $pattern)
				 	
				 	yield $pattern;
			else yield;
		}

		private function recursiveSearch(string $routeClass, string $routeState = "", bool $nestedMode = false):Route {

			$instance = $this->module->getClass($routeClass);

			$instance->_setAllow($this->module->routePermissions()); // this should be provisioned
			
			foreach ($this->loadPatterns($instance) as $pattern) {

				$routeList = call_user_func([$instance, $pattern]);

				$temporaryFullPath = $nestedMode ? "$routeState/$pattern": $pattern;

				if (!is_null($instance->prefixClass) && $this->prefixMatch($temporaryFullPath)) { // only delve deeper if we're on the right track i.e. if nested path = foo/bar/foobar, and nested method "bar" defines prefix, we only wanna explore its contents if requested route matches foo/bar

					$findNested = $this->recursiveSearch($instance->prefixClass, $temporaryFullPath, true);

					if ($findNested instanceof Route)

						return $findNested;
				}
				else {
					foreach ($routeList as $route) {

						$this->fullPath = $temporaryFullPath;

						if ($this->prefixMatch($this->fullPath) && $route->method == $this->httpMethod)

							return $route;
					}
					$instance->prefixClass = null; // reset ahead of other prefixed calls
				}
			}
		}

		private function prefixMatch(string $routeState):bool {
			
			return preg_match("/^$routeState/", $this->incomingPath); // account for placeholder patterns while matching i.e. replace and create a dynamic pattern

			// account for empty incoming and plug in "index"

			// leading slash should be optional
		}
		
		// i think this has to do with replacing path placeholders with parameters
		public function updateRequestParameters():void {

			// work with this->fullPath
		}

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
			
			foreach ($this->handlerParameters as $parameter => $argument) {
				
				if ($argument instanceof BaseRequest)
				
					$this->requestIndexInParameters = $parameter;

				elseif ( $this->databaseAdapter->isModel($argument))

					$this->modelIndexesInParameters[$parameter] = $argument;
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

			$request = $this->activeRoute->getRequest();
			
			foreach ($this->modelIndexesInParameters as $parameter => $model)

				$this->handlerParameters[ $parameter] = $this->databaseAdapter
				->findOne(
					$model::class, $request->$parameter // relies on the invocation ordering that populated request payload prior to calling this
				);
		}

		public function isApiRoute ():bool {

			return preg_match("/^" . $this->module->apiPrefix() . "/", $this->incomingPath);
		}

		// given a request to api/v3/verb/noun, return v3
		public function incomingVersion():string {
			
			$pattern = $this->module->apiPrefix() . "\/(.+?)\/";

			preg_match("/^" . $pattern . "/i", $this->incomingPath, $version);

			return $version[1];
		}

		# api/v3/verb/noun should return all versions from v3 and below
		private function apiVersionClasses():array {

			$versionKeys = array_keys($this->module->apiStack());

			$versionHandlers = array_values($this->module->apiStack());

			$start = array_search( // case-insensitive search

				strtolower($this->incomingVersion()),

				array_map("strtolower", $versionKeys)
			);

			$versionHandlers = array_slice($versionHandlers, $start, count($versionHandlers)-1);

			$versionKeys = array_slice($versionKeys, $start, count($versionKeys)-1);

			return array_combine($versionKeys, $versionHandlers);
		}

		// @return Strings[]
		private function entryRouteMap():array {
			
			if ($this->isApiRoute()) {

				$this->stripApiPrefix();

				return $this->apiVersionClasses();
			}
			return [$this->module->getAppMainRoutes()];
		}

		// given a request to api/v3/verb/noun, return verb/noun
		private function stripApiPrefix():void {
			
			$pattern = $this->module->apiPrefix() . "\/.+?\/(.+)";

			preg_match("/^" . $pattern . "/i", $this->incomingPath, $path);
			
			$this->incomingPath = $path[1];
		}
	}
?>