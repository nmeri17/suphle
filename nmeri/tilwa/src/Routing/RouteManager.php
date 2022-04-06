<?php
	namespace Tilwa\Routing;

	use Tilwa\Routing\Structures\PlaceholderCheck;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\{Auth\AuthStorage, Config\Router as RouterConfig, Routing\RouteCollection, Presentation\BaseRenderer, IO\Session};

	use Tilwa\Response\Format\Markup;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Exception\Explosives\{Miscellaneous\IncompatiblePatternReplacement, IncompatibleHttpMethod};

	class RouteManager {

		const PREV_RENDERER = 'prv_rdr';

		private $indexMethod = "_index",

		$config, $activeRenderer, $payload,

		$requestDetails, $fullTriedPath, $container,

		$activePlaceholders, $patternIndicator,

		$sessionClient;

		function __construct(RouterConfig $config, Container $container, RequestDetails $requestDetails, PathPlaceholders $placeholderStorage, PatternIndicator $patternIndicator, Session $sessionClient) {

			$this->config = $config;

			$this->container = $container;

			$this->requestDetails = $requestDetails;

			$this->activePlaceholders = $placeholderStorage;

			$this->patternIndicator = $patternIndicator;

			$this->sessionClient = $sessionClient;
		}

		public function findRenderer ():void {

			foreach ($this->entryRouteMap() as $collection) {
				
				$hit = $this->recursiveSearch($collection);

				if (!is_null($hit)) {

					$hit->setPath($this->requestDetails->getPath());

					$this->activeRenderer = $hit;

					return;
				}

				$this->patternIndicator->resetIndications();
			}
		}

		private function recursiveSearch (string $collectionName, string $incomingPath = null):?BaseRenderer {

			if (is_null($incomingPath))

				$incomingPath = trim($this->requestDetails->getPath(), "/");

			$collection = $this->container->getClass($collectionName);

			$matchingCheck = $this->findMatchingMethod( $incomingPath, $collection->_getPatterns() );

			if (is_null($matchingCheck)) return null;

			$methodName = $matchingCheck->getMethodName();

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

			foreach ($methodRegexes as $methodPattern => $regexForm) { // not using in_array or ^$ since method is not guaranteed to match entire string

				$safeRegex = str_replace("/", "\/", $regexForm);

				preg_match("/^$safeRegex/i", $currentRouteState, $matches); // avoid matches that just appear in the middle of the method instead of being the method

				if (!empty($matches)) {preg_match_all("/^$safeRegex/i", $currentRouteState, $matches2);
var_dump($matches, $safeRegex, $matches2); // we should catch and log placeholders and their real values here
					$matchedSegment = $matches[0];

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

				return preg_match("/[a-z]/", $pattern);
			});

			$values = array_map(function ($pattern) {

				return $this->regexForm($pattern);
			}, $methods);

			return array_combine($methods, $values);
		}

		/* given hypothetical path: PATH_id_EDIT_id2_EDIT__SAME__OKJh_optionalO_TOMP, clean and return a path similar to a real life path; but still in a regex format so optional segments can be indicated as such
		PATH/[\w-]+/EDIT/[\w-]+/EDIT-SAME-OKJ/?([\w-]+/)?TOMP
		*/
		public function regexForm (string $routeState):string {

			$segmentDelimiters = ["h" => "-", "u" => "_"];

			$placeholderCharacter = "[\w-]+";

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
				(?:_)(?<is_index>
					index$
				)# should come before next group so placeholder doesn't grab it. must be at end of the string
			)?
			(
				(?:_)?# path segments delimited by single underscores
				(?<placeholder>
					[a-z0-9]+
					(?<is_optional>[O])?
				)
				_?# possible trailing slash before next literal
			)?";

			$regexified = preg_replace_callback("/$pattern/x", function ($matches) use ( $segmentDelimiters, $placeholderCharacter) {

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

						$hasPlaceholder = rtrim($hasPlaceholder, "O");

						$builder .= "?" . // weaken trailing slash of preceding pattern

						"($placeholderCharacter$slash?)?";

						$this->activePlaceholders->pushSegment($hasPlaceholder); // not entirely correct. we should intercept them during the pregmatch
					}

					else {

						$builder .= "$placeholderCharacter$slash";

						$this->activePlaceholders->pushSegment($hasPlaceholder);
					}
				}

				if (isset($matches["is_index"]))

					$builder .= "";

				return $builder;
			}, $routeState);

			if (str_ends_with($regexified, "/"))

				$regexified = trim($regexified, "/") . "/?"; // make trailing slash optional
			
			return $regexified;
		}

		private function extractRenderer (RouteCollection $collection, string $remainder, string $methodName, bool $expectsCrud):?BaseRenderer {

			$possibleRenderers = $collection->_getLastRegistered();

			if ($expectsCrud) {

				$methodName = $this->findActiveCrud(array_keys($possibleRenderers), $remainder);

				$renderer = $possibleRenderers[$methodName];
			}

			else $renderer = $possibleRenderers[0];

			if ($this->confirmRouteMethod($renderer)) {

				$this->onSearchHit($collection, $renderer, $methodName);

				return $renderer;
			}
		}

		private function findActiveCrud (array $routePatterns, string $remainderPath):?string {

			$matchingCheck = $this->methodPartiallyMatchPattern($remainderPath, $routePatterns);

			if (
				!is_null($matchingCheck) &&

				$remainderPath == $this->matchRemainder($matchingCheck, $remainderPath ) // no leftover
			)

				return $matchingCheck->getMethodName();
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

		private function onSearchHit (RouteCollection $collection, BaseRenderer $renderer, string $pattern):void {

			$this->indicatorProxy($collection, $pattern);

			if ($this->isMirroring() && $renderer instanceof Markup)

				$renderer->setWantsJson();

			$container = $this->container;

			$renderer->setControllingClass(

				$container->getClass($collection->_handlingClass())
			);
			
			$renderer->hydrateDependencies($container);
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

		public function getIndicator ():PatternIndicator {

			return $this->patternIndicator;
		}
	}
?>