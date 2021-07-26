<?php

	namespace Tilwa\Routing;

	use Tilwa\App\Container;

	use Generator;

	use Tilwa\Contracts\{AuthStorage, Config\Router as RouterConfig, RouteCollection};

	use Tilwa\Middleware\MiddlewareRegistry;

	use Tilwa\Request\{BaseRequest, PathAuthorizer};

	use Tilwa\Response\Format\AbstractRenderer;

	class RouteManager {

		const PREV_RENDERER = 'prev_renderer';

		const PREV_REQUEST = 'prev_request';

		private $config, $activeRenderer, $payload,

		$requestDetails, $fullTriedPath, $container,

		$patternAuthentication, $registry, $authorizer;

		function __construct(RouterConfig $config, Container $container, RequestDetails $requestDetails, MiddlewareRegistry $registry, PathAuthorizer $authorizer) {

			$this->config = $config;

			$this->container = $container;

			$this->requestDetails = $requestDetails;

			$this->registry = $registry;

			$this->authorizer = $authorizer;
		}

		public function findRenderer ():void {

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$hit->setPath($this->fullTriedPath);

					$this->activeRenderer = $hit;

					return;
				}
			}
		}

		public function loadPatterns(RouteCollection $collection):Generator {
			
			// we can't skip collections where incoming path is not one of the patterns in AuthStorage->claimedRoutes (which would've improved matching speed) cuz of the complexity of such comparison
			foreach ($collection->getPatterns() as $pattern)
			 	
			 	yield $pattern;
		}

		/**
		* to find from cache, we won't need:
			- to parse our route before matching
			- loadPatterns?
		*/
		private function recursiveSearch(string $patternsCollection, string $routeState = "", string $invokerPrefix = "", bool $fromCache = false):AbstractRenderer {

			$collection = $this->container
			
			->getClass($patternsCollection, true);

			$patternPrefix = $invokerPrefix ?? $collection->_prefixCurrent();

			$collection->_setLocalPrefix($patternPrefix);
			
			foreach ($this->loadPatterns($collection) as $pattern) {

				$rendererList = call_user_func([$collection, $pattern]);
				/*
					- pair empty incoming path with _index method
					- crud methods disregard their method names
				*/
				if (($pattern == "_index") || $collection->_expectsCrud())

					$computedPattern = "";

				else $computedPattern = $pattern;

				if (!empty($patternPrefix))

					$computedPattern = "$patternPrefix/$computedPattern";

				$fullRouteState = "$routeState/$computedPattern";

				$parsed = $this->regexForm($fullRouteState);

				if (!is_null($collection->_getPrefixCollection()) && $this->prefixMatch($parsed)) { // only delve deeper if we're on the right track i.e. if nested path = foo/bar/foobar, and nested method "bar" defines prefix, we only wanna explore its contents if requested route matches foo/bar

					$this->indicatePatternDetails($collection, $pattern);

					return $this->recursiveSearch($collection->_getPrefixCollection(), $fullRouteState, $computedPattern); /** we don't bother checking whether a route was found or not because if there was none after going downwards*, searching sideways* won't help either

					 * downwards = deeper into a collection
					 * sideways = other patterns on this same collection
					*/
				}
				else {
					foreach ($rendererList as $path => $renderer) { // we'll usually get one route here, except for CRUD invocations

						if ($collection->_expectsCrud())

							$parsed .= $this->regexForm($path);

						if ($this->routeCompare($parsed, $renderer->getRouteMethod())) {

							$this->indicatePatternDetails($collection, $pattern);

							$this->fullTriedPath = strtolower($parsed);

							if ($this->requestDetails->isApiRoute() && $collection->_isMirroring())

								$renderer->contentIsNegotiable();
							
							return $this->bootRenderer($renderer, $collection->_handlingClass());
						}
					}

					$collection->_doesntExpectCrud(); // for subsequent patterns
				}
			}
		}

		private function routeCompare(string $path, string $rendererMethod):bool {

			$matchingPaths = $this->prefixMatch($path);

			$matchingMethods = $rendererMethod == $this->requestDetails->getMethod();

			if ($matchingPaths && !$matchingMethods)

				throw new IncompatibleHttpMethod( $rendererMethod);

			return $matchingPaths && $matchingMethods;
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

					$builder .=  "$default";
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

		private function prefixMatch (string $fullRouteState):bool {

			$escaped = preg_quote($fullRouteState, "/");

			return preg_match("/^$escaped?# neutralize trailing slash in replaced path
				/ix", $this->requestDetails->getPath());
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

		// @return Strings[]
		private function entryRouteMap():array {

			$requestDetails = $this->requestDetails;
			
			if ($requestDetails->isApiRoute()) {

				$requestDetails->stripApiPrefix();

				return $requestDetails->apiVersionClasses();
			}

			return [$this->config->browserEntryRoute()];
		}

		public function acceptsJson():bool {

			foreach (getallheaders() as $key => $value) {
				
				if (strtolower($key) == "accept" && preg_match("/application\/json/i", $value))

					return true;
			}
			return false;
		}

		private function bootRenderer(AbstractRenderer $renderer, string $controllingClass):AbstractRenderer {

			$rendererName = get_class($renderer);

			$parameters = $this->provideRendererDependencies($rendererName, $controllingClass)
			
			->getMethodParameters("setDependencies", $rendererName);

			return $renderer->setDependencies(...array_values($parameters));
		}

		private function provideRendererDependencies(string $renderer, string $controller):Container {

			return $this->container->whenType($renderer)

			->needsArguments([
				"controllerClass" => $controller
			]);
		}

		// if a higher level security was applied to a child collection with its own rules, omitting the current pattern, the security will be withdrawn from that pattern
		private function setPatternAuthentication(RouteCollection $collection, string $pattern):void {

			if ($activePatterns = $collection->_authenticatedPaths()) {

				if (in_array($pattern, $activePatterns))

					$this->patternAuthentication = $collection->_getAuthenticator();

				else $this->patternAuthentication = null;
			}
		}

		private function indicatePatternDetails (RouteCollection $collection, string $pattern) {

			$this->setPatternAuthentication($collection, $pattern);

			$this->includeMiddleware($collection, $pattern);

			$this->updatePermissions($collection, $pattern);
		}

		public function getPatternAuthentication ():AuthStorage {

			return $this->patternAuthentication;
		}

		private function includeMiddleware (RouteCollection $collection, string $segment):void {

			$collection->_assignMiddleware();

			$this->registry->updateStack($segment);
		}

		private function updatePermissions (RouteCollection $collection, string $pattern):void {

			$collection->_authorizePaths();

			$this->authorizer->updateRuleStatus($pattern);
		}
	}
?>