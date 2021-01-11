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

		private $fullTriedPath;

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

		public function loadPatterns(RouteCollection $collection):Generator {

			if ($collection->_passover())
			
				foreach ($collection->getPatterns() as $pattern)
				 	
				 	yield $pattern;
			else yield;
		}

		/**
		* to find from cache, we won't need:
			- to parse our route before matching
			- loadPatterns?
		*/
		private function recursiveSearch(string $routeClass, string $routeState = "", bool $nestedMode = false, bool $fromCache = false):Route {

			$collection = $this->provideCollection($routeClass);
			
			foreach ($this->loadPatterns($collection) as $pattern) {

				$routeList = call_user_func([$collection, $pattern]);

				if ($pattern == "_index") $pattern = ""; // pair empty incoming path with _index method

				$newRouteState = $nestedMode ? "$routeState/$pattern": $pattern;

				$parsed = $this->regexForm($newRouteState);

				if (!is_null($collection->prefixClass) && $this->prefixMatch($parsed)) { // only delve deeper if we're on the right track i.e. if nested path = foo/bar/foobar, and nested method "bar" defines prefix, we only wanna explore its contents if requested route matches foo/bar

					return $this->recursiveSearch($collection->prefixClass, $newRouteState, true); // we don't bother checking whether a route was found or not because if there was none after going downwards, searching sideways won't help either
				}
				else {
					foreach ($routeList as $route) { // we'll usually get one route here, except for CRUD routes

						if ($this->routeCompare($parsed, $route->method)) {

							$this->fullTriedPath = $parsed;

							if ($collection->isMirroring)

								$route->isContentNegotiable();
							
							return $route->setController($collection->_handlingClass());
						}
					}
				}
			}
		}

		private function routeCompare(string $path, string $routeMethod):bool {
			
			return $this->prefixMatch($path) && $routeMethod == $this->httpMethod;
		}

		// given hypothetic path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP
		private function regexForm(string $routeState):string {

			$segmentDelimiters = ["h" => "-", "u" => "_"];

			$pattern = "(
				(_)?#literal to literal i.e. no placeholder in between
				(?<one_word>
					[A-Z0-9]+# one word match
					(
						(
							_{2}[A-Z0-9]+)+# chain as many uppercase characters
							(?<merge_delimiter>[hu])?# double underscores with uppercase letters ending with any of these will be replaced with their counterparts
					)?# compound word
				)
			)?# literal match
			(
				(?:_)?# path segments delimited by single underscores
				(?<placeholder>
					[a-z0-9]+
					(?<is_optional>[O])?
				)
				_?# possible trailing slash before next literal
			)?";

			return preg_replace_callback("/$pattern/x", function ($matches) use ( $segmentDelimiters) {

				$builder = "";
				
				if ($default = @$matches["one_word"]) {

					if ($delimiter = @$matches["merge_delimiter"])

						$default = implode(
							$segmentDelimiters[$delimiter], explode(
								"__", rtrim($default, $delimiter) // trailing "h"
							)
						);

					$builder .=  "$default\/"; // the slash here is probably unrequired since the recursive loop adds that already
				}
				$wordPattern = "[a-z0-9]+?\/";

				$hasPlaceholder = @$matches["placeholder"];

				if ($maybe = @$matches["is_optional"]) {

					$hasPlaceholder = rtrim($hasPlaceholder, "O");

					$builder .= "($wordPattern)?";
				}
				elseif ($hasPlaceholder) $builder .= $wordPattern;

				return $builder;
			}, $routeState);
		}

		private function prefixMatch (string $newRouteState):bool {
			
			return preg_match("/^$newRouteState
				?# neutralize trailing slash in replaced path
				/ix", $this->incomingPath);
		}
		
		public function updateRequestParameters(Route $route):void {
			$pattern = "(?<![A-Z0-9])# negative lookbehind: given PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, refuse to match the h in the compound segment
			([a-z0-9]+)# pick placeholders";

			preg_match("/$pattern/x", $this->fullTriedPath, $matches);

			$route->getRequest()->setPayload($matches[0]);
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

		private function provideCollection(string $routeCollection):RouteCollection {

			$module = $this->module;
			
			$module->whenType(RouteCollection::class)

			->needsArguments([ // not passing the `preserve` argument so subsequent requests for this base type won't initialize these values afresh
				"permissions" => function($module) {

					return $module->getClass($module->routePermissions());
				},
				"browserEntry" => function($module) {

					return $module->browserEntryRoute();
				}
			]);
			return $module->getClass($routeCollection);
		}
	}
?>