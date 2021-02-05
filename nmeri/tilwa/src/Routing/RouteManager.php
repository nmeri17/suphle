<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Bootstrap;

	use Tilwa\Contracts\Orm;

	use Tilwa\Http\Response\Format\Markup;

	use \Generator;

	class RouteManager {

		private $module;

		private $activeRenderer;

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

		public function findRenderer ():AbstractRenderer {

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$this->updateRequestParameters($hit);

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
		private function recursiveSearch(string $patternsCollection, string $routeState = "", string $invokerPrefix = "", bool $fromCache = false):AbstractRenderer {

			$collection = $this->provideCollection($patternsCollection);

			$patternPrefix = $invokerPrefix ?? $collection->_prefixCurrent();

			$collection->_setLocalPrefix($patternPrefix);
			
			foreach ($this->loadPatterns($collection) as $pattern) {

				$rendererList = call_user_func([$collection, $pattern]);
				/*
					- pair empty incoming path with _index method
					- crud methods disregard their method names
				*/
				if (($pattern == "_index") || $collection->expectsCrud) $pattern = "";

				if (!empty($patternPrefix) ) $pattern = "$patternPrefix/$pattern";

				$newRouteState = $invokerPrefix ? "$routeState/$pattern": $pattern;

				$parsed = $this->regexForm($newRouteState);

				if (!is_null($collection->prefixClass) && $this->prefixMatch($parsed)) { // only delve deeper if we're on the right track i.e. if nested path = foo/bar/foobar, and nested method "bar" defines prefix, we only wanna explore its contents if requested route matches foo/bar

					return $this->recursiveSearch($collection->prefixClass, $newRouteState, $pattern); // we don't bother checking whether a route was found or not because if there was none after going downwards, searching sideways won't help either
				}
				else {
					foreach ($rendererList as $path => $renderer) { // we'll usually get one route here, except for CRUD invocations

						if ($collection->expectsCrud)

							$parsed .= $this->regexForm($path);

						if ($this->routeCompare($parsed, $renderer->routeMethod)) {

							$this->fullTriedPath = $parsed;

							if ($renderer instanceof Markup && $collection->isMirroring)

								$renderer->contentIsNegotiable();
							
							return $this->bootRenderer($renderer, $collection->_handlingClass());
						}
					}
					$collection->expectsCrud = null; // for subsequent patterns
				}
			}
		}

		private function routeCompare(string $path, string $rendererMethod):bool {
			
			return $this->prefixMatch($path) && $rendererMethod == $this->httpMethod;
		}

		/* given hypothetic path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regec format so optional segments can be indicated as such
		PATH/id/EDIT/id2/EDIT-SAME-OKJ/TOMP
		*/
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
		
		public function updateRequestParameters(AbstractRenderer $renderer):void {
			$pattern = "(?<![A-Z0-9])# negative lookbehind: given PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, refuse to match the h in the compound segment
			([a-z0-9]+)# pick placeholders";

			preg_match("/$pattern/x", $this->fullTriedPath, $matches);

			$renderer->getRequest()->setPayload($matches[0]);
		}

		public function setPrevious(AbstractRenderer $renderer ):static {

			$_SESSION['prev_route'] = $renderer;

			return $this;
		}

		public function getPrevious ():Route {

			return $_SESSION['prev_route'];
		}

		public function getActiveRenderer ():Route {

			return $this->activeRenderer;
		}

		public function setActiveRenderer (AbstractRenderer $renderer):self {

			$this->activeRenderer = $renderer;

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
		* @return previous AbstractRenderer
		*/
		public function mergeWithPrevious(BaseRequest $request):AbstractRenderer {
			
			$renderer = $this->getPrevious();

			$renderer->getRequest()

			->setValidationErrors( $request->validationErrors() );

			return $renderer;
		}

		public function prepareArguments():array {

			$renderer = $this->activeRenderer;

			$this->handlerParameters = $this->module->getMethodParameters($renderer->handler, $renderer->getController());

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

			$this->activeRenderer->setRequest ($request);
		}

		/*
		* @description: assumes ordering of the arguments on the action handler matches the one on url pattern

			handler (BaseRequest, Model1, Random, Model2)
			path/2/action/3 = [2,3]
		*/
		private function hydrateModels():void {

			$request = $this->activeRenderer->getRequest();
			
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

		private function provideCollection(string $rendererCollection):RouteCollection {

			$module = $this->module;
			
			$module->whenType(RouteCollection::class)

			->needsArguments([
				"permissions" => function($module) {

					return $module->getClass($module->routePermissions());
				},
				"browserEntry" => function($module) {

					return $module->browserEntryRoute();
				}
			], false);
			return $module->getClass($rendererCollection);
		}

		public function acceptsJson():bool {

			foreach (getallheaders() as $key => $value) {
				
				if (strtolower($key) == "accept" && preg_match("/application\/json/i", $value))

					return true;
			}
		}

		private function bootRenderer(AbstractRenderer $renderer, string $controllingClass):AbstractRenderer {

			$dependencyMethod = "setDependencies";
			
			$parameters = $this->module->getMethodParameters($dependencyMethod, $renderer::class);

			$controller = "controllerClass";

			if (array_key_exists($controller, $parameters) && empty($parameters[$controller]))

				$parameters[$controller] = $controllerClass;

			return call_user_func_array([$renderer, $dependencyMethod], $parameters);
		}
	}
?>