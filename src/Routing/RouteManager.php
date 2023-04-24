<?php
	namespace Suphle\Routing;

	use Suphle\Routing\{Structures\PlaceholderCheck, Decorators\HandlingCoordinator};

	use Suphle\Hydration\{Container, Structures\ObjectDetails};

	use Suphle\Contracts\{ Config\Router as RouterConfig, Routing\RouteCollection};

	use Suphle\Contracts\Presentation\{MirrorableRenderer, BaseRenderer};

	use Suphle\Request\RequestDetails;

	use Suphle\Exception\Explosives\IncompatibleHttpMethod;

	use Suphle\Services\Decorators\BindsAsSingleton;

	/**
	 * Can't replace this with a double in a http test cuz it'll get overriden when path is received. Anything exceeding capabilities of this class should be tested high level/behavior-wise rather than relying on specifics
	*/
	#[BindsAsSingleton]
	class RouteManager {

		public const PLACEHOLDER_REPLACEMENT = "[\w-]+";

		protected array $visitedMethods = [];

		protected ?BaseRenderer $activeRenderer = null;

		public function __construct (

			protected readonly RouterConfig $config,

			protected readonly Container $container,

			protected readonly RequestDetails $requestDetails,

			protected readonly PathPlaceholders $placeholderStorage,

			protected readonly PatternIndicator $patternIndicator,

			protected readonly CollectionMethodToUrl $urlReplacer,

			protected readonly ObjectDetails $objectMeta
		) {

			//
		}

		public function findRenderer ():void {

			$collectionList = $this->entryRouteMap();

			$requestPath = trim($this->requestDetails->getPath(), "/"); // this should only be read after setting collection list since it can mutate request path

			foreach ($collectionList as $collection) {

				$hit = $this->recursiveSearch($collection, $requestPath);

				if (!is_null($hit)) {

					$this->activeRenderer = $hit;

					return;
				}

				$this->finishCollectionHousekeeping();
			}
		}

		private function recursiveSearch (string $collectionName, string $incomingPath, string $parentPrefix = "" ):?BaseRenderer {

			$collection = $this->container->getClass($collectionName);

			$collection->_setParentPrefix($parentPrefix);

			$matchingCheck = $this->findMatchingMethod( $incomingPath, $collection->_getPatterns() );

			if (is_null($matchingCheck)) return null;

			$methodName = $this->visitedMethods[] = $matchingCheck->getMethodName();

			$collection->_invokePattern($methodName);

			$remainder = $this->matchRemainder($matchingCheck, $incomingPath);

			$expectsCrud = $collection->_expectsCrud();

			if (!empty($remainder) && !$expectsCrud) {

				$prefixClass = $collection->_getPrefixCollection();

				if (!$prefixClass) return null;

				$this->patternIndicator->logPatternDetails($collection, $methodName);

				return $this->recursiveSearch(
					$prefixClass, $remainder,

					$collection->_prefixCurrent()
				);
			}

			return $this->extractRenderer($collection, $remainder, $methodName, $expectsCrud);
		}

		public function findMatchingMethod ( string $fullRouteState, array $patterns):?PlaceholderCheck {

			$literalMatch = strtoupper($fullRouteState);

			if ( in_array($literalMatch, $patterns))

				return new PlaceholderCheck($fullRouteState, $literalMatch);
			
			$indexMethodIndex = array_search(RouteCollection::INDEX_METHOD, $patterns);

			if ($indexMethodIndex !== false) {

				if ($fullRouteState == "" )

					return new PlaceholderCheck("", RouteCollection::INDEX_METHOD );

				unset($patterns[$indexMethodIndex]); // since we're sure it's not the one, no need to confuse the other guys, who will always "partially" match an empty string
			}

			return $this->methodPartiallyMatchPattern($fullRouteState, $patterns);
		}

		public function methodPartiallyMatchPattern (string $currentRouteState, array $patterns):?PlaceholderCheck {

			$methodRegexes = $this->patternPlaceholderDetails($patterns);

			foreach ($methodRegexes as $methodPattern => $methodDetails) { // not using in_array or ^$ since method is not guaranteed to match entire string

				$safeRegex = str_replace("/", "\/", (string) $methodDetails["url"]);

				preg_match("/^$safeRegex/i", $currentRouteState, $matches); // ^ == avoid matches that just appear in the middle of the method instead of being the method

				if (!empty($matches)) {

					$matchedSegment = $matches[0];
				
					$this->placeholderStorage->foundSegments($methodDetails["placeholders"]);

					return new PlaceholderCheck($matchedSegment, $methodPattern );
				}
			}

			return null;
		}

		public function patternPlaceholderDetails (array $patterns):array {

			$values = array_map(function ($pattern) {

				$result = $this->urlReplacer->replacePlaceholders($pattern, self::PLACEHOLDER_REPLACEMENT);

				return [
					"url" => $result->regexifiedUrl(),

					"placeholders" => $result->getPlaceholders()
				];
			}, $patterns);

			return array_combine($patterns, $values);
		}

		private function extractRenderer (RouteCollection $collection, string $remainder, string $methodName, bool $expectsCrud):?BaseRenderer {

			$possibleRenderers = $collection->_getLastRegistered();

			if (empty($possibleRenderers)) // url segments matched collection methods but no renderer was registered e.g. in a canary list where request satisfies none of the rules. Instead of throwing an error there to confirm to visitor that path exists, we allow it fail silently

				return null;

			if ($expectsCrud) {

				$methodName = $this->findActiveCrud(array_keys($possibleRenderers), $remainder);

				if (!array_key_exists($methodName, $possibleRenderers)) // invalid path or segment received for a crud path

					return null;

				$renderer = $possibleRenderers[$methodName];
			}

			else $renderer = $possibleRenderers[0];

			if ($this->confirmRouteMethod($renderer)) {

				$this->onSearchCompletion($collection, $renderer, $methodName);

				return $renderer;
			}

			return null;
		}

		public function findActiveCrud (array $routePatterns, string $remainderPath):?string {

			$matchingCheck = $this->findMatchingMethod($remainderPath, $routePatterns);

			if (
				!is_null($matchingCheck) &&

				empty($this->matchRemainder($matchingCheck, $remainderPath) // no leftover
				)
			)

				return $matchingCheck->getMethodName();

			return null;
		}

		private function matchRemainder (PlaceholderCheck $check, string $fullRouteState):string {

			if (empty($fullRouteState) && $check->getMethodName() == RouteCollection::INDEX_METHOD)

				return $fullRouteState;

			return explode($check->getRouteState(), $fullRouteState, 2)[1];
		}

		public function confirmRouteMethod (BaseRenderer $renderer):bool {

			$rendererMethod = $renderer->getRouteMethod();

			if ( !$this->requestDetails->matchesMethod($rendererMethod))

				throw new IncompatibleHttpMethod( $rendererMethod);

			return true;
		}

		private function onSearchCompletion (RouteCollection $collection, BaseRenderer $renderer, string $pattern):void {

			$this->patternIndicator->logPatternDetails($collection, $pattern);

			if (
				$this->patternIndicator->shouldMirror() &&

				$renderer instanceof MirrorableRenderer
			)

				$renderer->setWantsJson();

			$container = $this->container;

			$attributesList = $this->objectMeta->getClassAttributes(

				$collection::class, HandlingCoordinator::class
			);

			$handlingClass = end($attributesList)->newInstance();

			$renderer->setCoordinatorClass(

				$container->getClass($handlingClass->coordinatorName)
			);

			$this->placeholderStorage->setMethodSegments($this->visitedMethods);
		}

		public function getActiveRenderer ():?BaseRenderer {

			return $this->activeRenderer;
		}

		/**
		 * @return class-string<RouteCollection>[]
		*/
		public function entryRouteMap ():array {

			$requestDetails = $this->requestDetails;

			$entryRoute = $this->config->browserEntryRoute();

			$hasEntry = !is_null($entryRoute);
			
			if (!$requestDetails->isApiRoute()) {

				$browserRoutes = [];

				if ($hasEntry) $browserRoutes[] = $entryRoute;

				return $browserRoutes;
			}

			$apiStack = $requestDetails->apiVersionClasses();

			if ($this->patternIndicator->shouldMirror() && $hasEntry)

				array_push($apiStack, $entryRoute); // entry goes to the bottom

			$requestDetails->stripApiPrefix(); // just before we go on our search

			return $apiStack;
		}

		/**
		 * Get the instance we've made changes to
		*/
		public function getPlaceholderStorage ():PathPlaceholders {

			return $this->placeholderStorage;
		}

		public function finishCollectionHousekeeping ():void {

			$this->patternIndicator->resetIndications();

			$this->placeholderStorage->clearAllSegments();

			$this->visitedMethods = [];
		}
	}
?>