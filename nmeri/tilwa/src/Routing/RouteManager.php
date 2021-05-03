<?php

	namespace Tilwa\Routing;

	use Tilwa\App\{ParentModule, Container};

	use Tilwa\Http\Response\Format\Markup;

	use Generator;

	class RouteManager {

		const PREV_RENDERER = 'prev_renderer';

		const PREV_REQUEST = 'prev_request';

		private $module, $activeRenderer, $payload,

		$incomingPath, $httpMethod, $fullTriedPath,

		$collectionArguments, $container;

		function __construct(ParentModule $module, Container $container, string $incomingPath, string $httpMethod ) {

			$this->module = $module;

			$this->incomingPath = $incomingPath;

			$this->httpMethod = $httpMethod;

			$this->container = $container;
		}

		public function findRenderer ():AbstractRenderer {

			$this->defineCollectionArguments();

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$hit->setPath($this->fullTriedPath);

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

		/* given hypothetical path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regex format so optional segments can be indicated as such
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

		public function setPrevious(AbstractRenderer $renderer , BaseRequest $request):self {

			$_SESSION[self::PREV_RENDERER] = $renderer;

			$_SESSION[self::PREV_REQUEST] = $request;

			return $this;
		}

		public function getPreviousRenderer ():AbstractRenderer {

			return $_SESSION[self::PREV_RENDERER];
		}

		public function getPreviousRequest ():BaseRequest {

			return $_SESSION[self::PREV_REQUEST];
		}

		public function getActiveRenderer ():AbstractRenderer {

			return $this->activeRenderer;
		}

		public function setActiveRenderer (AbstractRenderer $renderer):self {

			$this->activeRenderer = $renderer;

			return $this;
		}

		// note: we are not handling POST yet
		public function savePayload():self {
			
			$this->payload = array_diff_key(["tilwa_path" => 55], $_GET);

			return $this;
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

		// @return concrete instance of given collection class containing list of patterns and renderers
		private function provideCollection(string $rendererCollection):RouteCollection {

			return $this->container->whenType($rendererCollection)

			->needsArguments($this->collectionArguments)
			
			->getClass($rendererCollection);
		}

		public function acceptsJson():bool {

			foreach (getallheaders() as $key => $value) {
				
				if (strtolower($key) == "accept" && preg_match("/application\/json/i", $value))

					return true;
			}
			return false;
		}

		private function bootRenderer(AbstractRenderer $renderer, string $controllingClass):AbstractRenderer {

			$dependencyMethod = "setDependencies";

			$parameters = $this->provideRendererDependencies($renderer::class, $controllingClass)
			
			->getMethodParameters($dependencyMethod, $renderer::class);

			return call_user_func_array([$renderer, $dependencyMethod], $parameters);
		}

		private function defineCollectionArguments() {

			$this->collectionArguments = [
				"permissions" => $this->container

				->getClass($this->module->routePermissions()),
				
				"browserEntry" => $this->module->browserEntryRoute()
			];
		}

		private function provideRendererDependencies(string $renderer, string $controller):Container {

			return $this->container->whenType($renderer)

			->needsArguments([
				"controllerClass" => $controller
			]);
		}
	}
?>