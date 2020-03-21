<?php

	namespace Tilwa\Route;

	use Tilwa\Controllers\Bootstrap;

	class RouteManager {

		/**
		* @property Bootstrap */
		private $app;

		/**
		* @property Route */
		private $activeRoute;

		function __construct(Bootstrap $app ) {

			$this->app = $app;
		}

		/**
		 * @param {reqPath}: does not support query urls
		 *
		 * @return Route|false
		 **/
		public function findRoute (string $reqPath, int $reqMethod ) {

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

			if (!$destination /*|| $this->activeRoute->equals($destination)*/) // check if there was no blocked request or if previous route failed some validations
				$destination = $this->findRoute( $fallback, Route::GET );
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

			return $this;
		}

		public function getPrevRequest () {

			$prev = @$_SESSION['prev_requests'];

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
	}

?>