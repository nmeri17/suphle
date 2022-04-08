<?php
	namespace Tilwa\Routing;

	use Tilwa\Routing\Structures\PlaceholderCheck;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{ Config\Router as RouterConfig, Routing\RouteCollection, Presentation\BaseRenderer, IO\Session};

	use Tilwa\Response\Format\Markup;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\IncompatibleHttpMethod;

	class RouteManager {

		const PREV_RENDERER = "prv_rdr",

		PLACEHOLDER_REPLACEMENT = "[\w-]+";

		private $indexMethod = "_index",

		$visitedMethods = [],

		$config, $activeRenderer, $requestDetails, $container,

		$placeholderStorage, $patternIndicator,

		$sessionClient, $urlReplacer;

		public function __construct(RouterConfig $config, Container $container, RequestDetails $requestDetails, PathPlaceholders $placeholderStorage, PatternIndicator $patternIndicator, Session $sessionClient, CollectionMethodToUrl $urlReplacer) {

			$this->config = $config;

			$this->container = $container;

			$this->requestDetails = $requestDetails;

			$this->placeholderStorage = $placeholderStorage;

			$this->patternIndicator = $patternIndicator;

			$this->sessionClient = $sessionClient;

			$this->urlReplacer = $urlReplacer;
		}

		public function findRenderer ():void {

			$requestPath = trim($this->requestDetails->getPath(), "/");

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection, $requestPath);

				if (!is_null($hit)) {

					$hit->setPath($requestPath);

					$this->activeRenderer = $hit;

					return;
				}

				$this->finishCollectionHousekeeping();
			}
		}

		private function recursiveSearch (string $collectionName, string $incomingPath ):?BaseRenderer {

			$collection = $this->container->instantiateConcrete($collectionName);

			$matchingCheck = $this->findMatchingMethod( $incomingPath, $collection->_getPatterns() );

			if (is_null($matchingCheck)) return null;

			$methodName = $matchingCheck->getMethodName();

			$this->visitedMethods[] = $methodName;

			$collection->$methodName();

			$remainder = $this->matchRemainder($matchingCheck, $incomingPath);

			$expectsCrud = $collection->_expectsCrud();

			if (!empty($remainder) && !$expectsCrud) {

				$prefixClass = $collection->_getPrefixCollection();

				if (!$prefixClass) return null;

				$this->indicatorProxy($collection, $methodName);

				return $this->recursiveSearch($prefixClass, $remainder);
			}

			return $this->extractRenderer($collection, $remainder, $methodName, $expectsCrud);
		}

		public function findMatchingMethod ( string $fullRouteState, array $patterns):?PlaceholderCheck {

			$literalMatch = strtoupper($fullRouteState);

			if ( in_array($literalMatch, $patterns))

				return new PlaceholderCheck($fullRouteState, $literalMatch);

			if ($fullRouteState == "" )

				return $this->indexMethodToPattern( $patterns);

			unset($patterns[array_search($this->indexMethod, $patterns)]); // since we're sure it's not the one, no need to confuse the other guys, who will always "partially" match an empty string

			return $this->methodPartiallyMatchPattern($fullRouteState, $patterns);
		}

		public function methodPartiallyMatchPattern (string $currentRouteState, array $patterns):?PlaceholderCheck {

			$methodRegexes = $this->getPlaceholderMethods($patterns);

			foreach ($methodRegexes as $methodPattern => $methodDetails) { // not using in_array or ^$ since method is not guaranteed to match entire string

				$safeRegex = str_replace("/", "\/", $methodDetails["url"]);

				preg_match("/^$safeRegex/i", $currentRouteState, $matches); // ^ == avoid matches that just appear in the middle of the method instead of being the method

				if (!empty($matches)) {

					$matchedSegment = $matches[0];
				
					$this->placeholderStorage->foundSegments($methodDetails["placeholders"]);

					return new PlaceholderCheck($matchedSegment, $methodPattern );
				}
			}

			return null;
		}

		private function indexMethodToPattern ( array $patterns):?PlaceholderCheck {

			if ( in_array($this->indexMethod, $patterns))

				return new PlaceholderCheck("", $this->indexMethod );

			return null;
		}

		public function getPlaceholderMethods (array $patterns):array {

			$methods = array_filter($patterns, function ($pattern) {

				return preg_match("/". CollectionMethodToUrl::PLACEHOLDER_IDENTIFIER ."/", $pattern);
			});

			$values = array_map(function ($pattern) {

				$result = $this->urlReplacer->replacePlaceholders($pattern, self::PLACEHOLDER_REPLACEMENT);


				return [
					"url" => $result->regexifiedUrl(),

					"placeholders" => $result->getPlaceholders()
				];
			}, $methods);

			return array_combine($methods, $values);
		}

		private function extractRenderer (RouteCollection $collection, string $remainder, string $methodName, bool $expectsCrud):?BaseRenderer {

			$possibleRenderers = $collection->_getLastRegistered();

			if ($expectsCrud) {

				$methodName = $this->findActiveCrud(array_keys($possibleRenderers), $remainder);

				$renderer = $possibleRenderers[$methodName];
			}

			else $renderer = $possibleRenderers[0];

			if ($this->confirmRouteMethod($renderer)) {

				$this->onSearchCompletion($collection, $renderer, $methodName);

				return $renderer;
			}

			return null;
		}

		private function findActiveCrud (array $routePatterns, string $remainderPath):?string {

			$matchingCheck = $this->methodPartiallyMatchPattern($remainderPath, $routePatterns);

			if (
				!is_null($matchingCheck) &&

				$remainderPath == $this->matchRemainder($matchingCheck, $remainderPath ) // no leftover
			)

				return $matchingCheck->getMethodName();

			return null;
		}

		private function matchRemainder (PlaceholderCheck $check, string $fullRouteState):string {

			if (empty($fullRouteState) && $check->getMethodName() == $this->indexMethod)

				return $fullRouteState;

			return explode($check->getRouteState(), $fullRouteState, 2)[1];
		}

		public function confirmRouteMethod (BaseRenderer $renderer):bool {

			$rendererMethod = $renderer->getRouteMethod();

			if ( !$this->requestDetails->matchesMethod($rendererMethod))

				throw new IncompatibleHttpMethod( $rendererMethod);

			return true;
		}

		private function isMirroring ():bool {

			return $this->requestDetails->isApiRoute() && $this->config->mirrorsCollections();
		}

		private function onSearchCompletion (RouteCollection $collection, BaseRenderer $renderer, string $pattern):void {

			$this->indicatorProxy($collection, $pattern);

			if ($this->isMirroring() && $renderer instanceof Markup)

				$renderer->setWantsJson();

			$container = $this->container;

			$renderer->setControllingClass(

				$container->getClass($collection->_handlingClass())
			);
			
			$renderer->hydrateDependencies($container);

			$this->placeholderStorage->setMethodSegments($this->visitedMethods);
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

		public function setPreviousRenderer(BaseRenderer $renderer):void {

			$this->sessionClient->setValue(self::PREV_RENDERER, $renderer);
		}

		public function getPreviousRenderer ():BaseRenderer {

			return $this->sessionClient->getValue(self::PREV_RENDERER);
		}

		public function getActiveRenderer ():?BaseRenderer {

			return $this->activeRenderer;
		}

		// @return Strings<RouteCollection>[]
		public function entryRouteMap():array {

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

		public function getIndicator ():PatternIndicator {

			return $this->patternIndicator;
		}

		public function finishCollectionHousekeeping ():void {

			$this->patternIndicator->resetIndications();

			$this->placeholderStorage->clearAllSegments();

			$this->visitedMethods = [];
		}
	}
?>