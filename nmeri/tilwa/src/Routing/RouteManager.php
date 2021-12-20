<?php
	namespace Tilwa\Routing;

	use Tilwa\App\Container;

	use Generator;

	use Tilwa\Contracts\{Auth\AuthStorage, Config\Router as RouterConfig, Routing\RouteCollection};

	use Tilwa\Response\Format\{AbstractRenderer, Markup};

	use Tilwa\Errors\{IncompatiblePatternReplacement, IncompatibleHttpMethod};

	class RouteManager {

		const PREV_RENDERER = 'prv_rdr';

		private $config, $activeRenderer, $payload,

		$requestDetails, $fullTriedPath, $container,

		$activePlaceholders, $patternIndicator;

		function __construct(RouterConfig $config, Container $container, RequestDetails $requestDetails, PathPlaceholders $placeholderStorage, PatternIndicator $patternIndicator) {

			$this->config = $config;

			$this->container = $container;

			$this->requestDetails = $requestDetails;

			$this->activePlaceholders = $placeholderStorage;

			$this->patternIndicator = $patternIndicator;
		}

		public function findRenderer ():void {

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$hit->setPath($this->fullTriedPath);

					$this->activeRenderer = $hit;

					return;
				}

				$this->patternIndicator->resetIndications();
			}
		}

		public function loadPatterns(RouteCollection $collection):Generator {
			
			foreach ($collection->_getPatterns() as $pattern)
			 	
			 	yield $pattern;
		}

		private function recursiveSearch(string $patternsCollection, string $routeState = "", string $invokerPrefix = ""/*, bool $fromCache = false*/):?AbstractRenderer {

			$collection = $this->container->getClass($patternsCollection);

			$patternPrefix = $invokerPrefix ?? $collection->_prefixCurrent();
			
			foreach ($this->loadPatterns($collection) as $pattern) {

				$computedPattern = $this->patternToUrlSegment($pattern, $patternPrefix);

				$fullRouteState = "$routeState/$computedPattern";

				$parsed = $this->regexForm($fullRouteState);

				if (!$this->prefixMatch($parsed)) continue;

				call_user_func([$collection, $pattern]);

				$nested = $collection->_getPrefixCollection();

				if (!is_null($nested)) {

					$this->indicatorProxy($collection, $pattern);

					return $this->recursiveSearch($nested, $fullRouteState, $computedPattern); /** we don't bother checking whether a route was found or not because if there was none after going downwards*, searching sideways* won't help either

					 * downwards = deeper into a collection
					 * sideways = other patterns on this same collection
					*/
				}
				
				foreach ($collection->_getLastRegistered() as $path => $renderer) { // we'll usually get one route here, except for CRUD invocations

					$routeMethod = $renderer->getRouteMethod();

					if ($collection->_expectsCrud()) {

						$collection->_setCrudPrefix($pattern);

						$isHit = $this->routeCompare($parsed . "/" . $this->regexForm($path), $routeMethod);
					}

					else $isHit = $this->routeCompare($parsed, $routeMethod);

					if ($isHit) {

						$this->onSearchHit($collection, $renderer, $pattern);

						return $renderer;
					}
				}
			}
		}

		/**
			- pair empty incoming path with _index method
		*/
		private function patternToUrlSegment (string $pattern, string $prefix):string {

			if ($pattern == "_index") $segment = "";

			else $segment = $pattern;

			if (!empty($prefix)) $segment = "$prefix/$segment";

			return $segment;
		}

		private function isMirroring ():bool {

			return $this->requestDetails->isApiRoute() && $this->config->mirrorsCollections();
		}

		private function onSearchHit (RouteCollection $collection, AbstractRenderer $renderer, string $pattern):void {

			$this->indicatorProxy($collection, $pattern);

			if ($this->isMirroring() && $renderer instanceof Markup)

				$renderer->setWantsJson();
			
			$this->bootRenderer($renderer, $collection->_handlingClass());
		}

		private function indicatorProxy (RouteCollection $collection, string $pattern):void {

			if ( $this->isMirroring())

				$this->patternIndicator->setDefaultAuthenticator(

					$this->container->getClass(

						$this->config->mirrorAuthenticator()
					)
				);

			$this->patternIndicator->indicate($collection, $pattern);
		}

		public function routeCompare(string $path, string $rendererMethod):bool {

			try {

				$this->fullTriedPath = strtolower($this->activePlaceholders->replaceInPattern($path));
			}
			catch (IncompatiblePatternReplacement $e) {

				return false;
			}

			$matchingPaths = $this->prefixMatch($this->fullTriedPath, true);

			$matchingMethods = $this->requestDetails->matchesMethod($rendererMethod);

			if ($matchingPaths && !$matchingMethods)

				throw new IncompatibleHttpMethod($this->requestDetails, $rendererMethod);

			return $matchingPaths && $matchingMethods;
		}

		/* given hypothetical path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regex format so optional segments can be indicated as such
		PATH/id/EDIT/id2/EDIT-SAME-OKJ/(optional/)?TOMP
		*/
		public function regexForm(string $routeState):string {

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

			return preg_replace_callback("/$pattern/x", function ($matches) use ( $segmentDelimiters, $routeState) {

				$builder = "";

				$slash = "/";
				
				if ($default = @$matches["one_word"]) {

					if ($delimiter = @$matches["merge_delimiter"])

						$default = implode(
							$segmentDelimiters[$delimiter], explode(
								"__", rtrim($default, $delimiter) // trailing "h"
							)
						);

					$builder .=  $default . $slash;
				}

				if ($hasPlaceholder = @$matches["placeholder"]) {

					if (!empty($matches["is_optional"])) {

						$hasPlaceholder = rtrim($hasPlaceholder, "O") . $slash;

						$builder .= "($hasPlaceholder)?";

						$this->activePlaceholders->pushSegment($hasPlaceholder);
					}

					else {

						$builder .= $hasPlaceholder . $slash;

						$this->activePlaceholders->pushSegment($hasPlaceholder);
					}
				}

				return $builder;
			}, $routeState);
		}

		/**
		 * @param {fullRouteState} Regex pattern. @see return value of [regexForm]
		 * */
		private function prefixMatch (string $fullRouteState, bool $fullMatch = false):bool {

			$escaped = preg_quote($fullRouteState, "/") . "\/?"; # neutralize trailing slash in replaced path

			if ($fullMatch) $escaped .= "$";
// var_dump($escaped, $this->requestDetails->getPath());
			return preg_match("/^$escaped/i", $this->requestDetails->getPath());
		}

		public function setPreviousRenderer(AbstractRenderer $renderer):self {

			$_SESSION[self::PREV_RENDERER] = $renderer;

			return $this;
		}

		public function getPreviousRenderer ():AbstractRenderer {

			return $_SESSION[self::PREV_RENDERER];
		}

		public function getActiveRenderer ():?AbstractRenderer {

			return $this->activeRenderer;
		}

		// @return Strings[]
		private function entryRouteMap():array {

			$requestDetails = $this->requestDetails;

			$config = $this->config;

			$entryRoute = $config->browserEntryRoute();
			
			if ($requestDetails->isApiRoute()) {

				$requestDetails->stripApiPrefix();

				$apiStack = $requestDetails->apiVersionClasses();

				if ($config->mirrorsCollections())

					$apiStack = array_push($apiStack, $entryRoute); // push it to the bottom

				return $apiStack;
			}

			return [$entryRoute];
		}

		private function bootRenderer(AbstractRenderer $renderer, string $controllingClass):void {

			$rendererName = get_class($renderer);

			$parameters = $this->provideRendererDependencies($rendererName, $controllingClass)
			
			->getMethodParameters("setDependencies", $rendererName);

			$renderer->setDependencies(...array_values($parameters));
		}

		private function provideRendererDependencies(string $renderer, string $controller):Container {

			return $this->container->whenType($renderer)

			->needsArguments([
				"controllerClass" => $controller
			]);
		}

		public function getIndicator ():PatternIndicator {

			return $this->patternIndicator;
		}
	}
?>