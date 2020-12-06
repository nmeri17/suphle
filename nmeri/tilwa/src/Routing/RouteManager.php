<?php

	namespace Tilwa\Routing;

	use Tilwa\Controllers\Bootstrap;

	class RouteManager {

		/**
		* @property Bootstrap */
		private $app;

		/**
		* @property Route */
		private $activeRoute;

		/**
		* @property array */
		private $requestLog; // use this to keep synchronic track of changes to the session request holder (which is only updated at the end of the request, i.e. updates of an altered session on another page fail to reflect)

		function __construct(Bootstrap $app ) {

			$this->app = $app;
		}

		/**
		 * @param {reqPath}: does not support query urls
		 *
		 * @return Route|false
		 **/
		public function findRoute (string $reqPath, int $reqMethod ) {

			if (preg_match('/^\/?$/', $reqPath))

				return $this->findRoute('index', $reqMethod);

			$regx = '/\{(\w+)?(\?)\}/';

			$paramPair = [];

			$allRoutes = $this->app->routeCatalog->registeredRoutes();

			// search register for route matching this pattern
			$target = @array_filter($allRoutes, function ($route) use ( $regx, $reqPath, $reqMethod, &$paramPair) {

				// convert /jui/{fsdf}/weeer to /jui/\w+/weeer
				// /jui/{fsdf?}/weeer to /jui/(\/\w+)?weeer
				$tempPat = preg_replace_callback($regx, function ($m) {

					$routeToken = $m[1];

					$wordPlcholdr = "(?P<$routeToken>\/\w+)"; // return assoc matches
					if (isset($m[2])) $wordPlcholdr .= '?';
var_dump($routeToken, $wordPlcholdr);
					return $wordPlcholdr;
				}, preg_quote($route->pattern) );

				$numMatches = preg_match_all("/^\/?$tempPat$/", $reqPath, $paramPair); // permit non-prefixed registered patterns to match requests for slash prefixed
					// var_dump($paramPair, $numMatches, $tempPat);
				return $numMatches !== 0 && $route->method === $reqMethod;
			});
//var_dump($target); die();
			$target = current($target);

			if ($target !== false) {

				$target->parameters = $paramPair;

				$target->setPath($reqPath);
			}

			return $target;
		}

		public function hinderedRequest (string $fallback):Route {

			$prevRequests = $_SESSION['prev_requests'];

			array_shift($prevRequests); // remove the checkpoint route

			$destination = current($prevRequests)['next_prev'];

			if (!$destination )

				$destination = $this->findRoute( $fallback, Route::GET);

			$this->requestLog = $prevRequests;

			return $destination;
		}

		// compares incoming request with the immediate one in session and if different, pushes the current request to the top of the stack ahead of the next request
		public function pushPrevRequest(Route $incomingRoute, array $routeData, bool $historyMode = false ):RouteManager {

			$prev = @$_SESSION['prev_requests'];

			if (http_response_code() !== 404) { // no need falling back to non existent paths

				if (!empty($prev) ) { // retain data in-between requests with different methods

					$oldRoute = $prev[0]['next_prev'];

					$oldData = $prev[0]['data'];

					$samePayload = strcasecmp(
						json_encode($oldData), json_encode($routeData)
					) === 0; // using this instead of array_diff_assoc cuz it throws errors on multidimensional arrays

					$matchesRoute = $oldRoute->equals($incomingRoute);

					if ( !$matchesRoute || !$samePayload) { // update ahead of next request only when current request changes

						if ($matchesRoute)

							$incomingRoute = $oldRoute; // we'll assume incoming route belongs to another method, and retain it
							//var_dump($incomingRoute, $routeData);

						$toSave = [

							'next_prev' => $incomingRoute,

							'data' => $routeData,

							'request_time' => date('H:i:s')
						];

						if (!$historyMode)

							$_SESSION['prev_requests'][0] = $toSave;

						elseif (!$matchesRoute && $historyMode)

							array_unshift($_SESSION['prev_requests'], $toSave);
					}
				}

				else {
					//var_dump($incomingRoute);

					$initRequest = [

						'next_prev' => $incomingRoute,

						'data' => $routeData,

						'request_time' => date('H:i:s')
					];

					$_SESSION['prev_requests'] = [$initRequest];
				}
			}

			$this->requestLog = $_SESSION['prev_requests'];

			return $this;
		}

		public function getPrevRequest () {

			$prev = $this->requestLog; // this relies on the trust that every session altering method will update this property

			if (!empty($prev) )

				return [

					'route' => $prev[0]['next_prev'],

					'data' => $prev[0]['data']
				];
		}

		public function getActiveRoute ():Route {

			return $this->activeRoute;
		}

		public function setActiveRoute (Route $route):RouteManager {

			$this->activeRoute = $route;

			return $this;
		}

		public function setPayload(array $defaultParameters = []) {
			
			$payloadAnchor = 'tilwa_request';

			$fullPayload = array_filter($_GET + $_POST, function ( $key) {

				return $key !== $payloadAnchor;
			}, ARRAY_FILTER_USE_KEY);

			unset($_GET[$payloadAnchor], $_POST[$payloadAnchor]);

			$this->activeRoute->getRequest()

			->replacePayload($fullPayload + $defaultParameters);

			return $this;
		}
	}

?>