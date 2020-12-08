<?php

	namespace Tilwa\Http\Response;

	
	use Phpfastcache\CacheManager;

	use Phpfastcache\Config\ConfigurationOption;

	use Tilwa\Route\Route;

	/*use Monolog\Logger;

	use Monolog\Handler\StreamHandler;*/
	use \Exception;


	class ResponseManager {

		protected $cachedData;

		/**
		* @var Tilwa\Controllers\Bootstrap */
		private $app;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}
		
		public function getResponse () {

			$container = $this->app;

			$router = $container->router;

			$request = $router->getActiveRoute()->getRequest();

			if (!$request->validated())

				$request->executeHandler(); // does this replace whatever data was stored in previous request with current payload?
			$router->pushPrevRequest($request); // refactor this internally to work with a full request
			
			if (
				!empty($validationErr) ||

				!is_null($requestedRoute->getRedirectDestination())
			) { // redirection will take precedence over viewless routes
				// this flow needs to change if we're breaking down the routes into separate classes

				if ($this->failedValidation)

					$requestedRoute->restorePrevPage = true;

				$this->changeDestination($request); // ensure `request` is altered in here
				// also refactor to work with new argument
			}

			return $request->renderResponse();
		}

		// returns a global instance of phpfastcache manager
		public function cacheManager() {

			//Configuring PHP Fast Cache
			CacheManager::setDefaultConfig(new ConfigurationOption([

				"path" =>  $this->app->rootPath ."/req-cache"
			]));

			return CacheManager::getInstance();
		}

		// if it's 'reload', replace current route with that matching user previous request. then merge the view data with what we have now
		private function changeDestination (Route $route, array $currViewData, array $validationErr) {

			$app = $this->app;

			$router = $app->router;

			$prevReqRoute = $router->getPrevRequest()['route'];

			if (is_null($prevReqRoute)) { // currently the 1st route

				var_dump($_SESSION['prev_requests'], $route );

				$prevReqRoute = $route;
			}

			if (!$route->restorePrevPage && !$this->failedValidation) {

				$destinationCallback = $route->getRedirectDestination();

				$destination = $destinationCallback($currViewData, function ($defaultRoute) {

					return $this->app->router->hinderedRequest($defaultRoute);
				});

				if (is_string($destination)) {

					if (strpos($destination,'://') !== false)

						return header('Location: '. $destination); // external redirect

					if (
						$destinationRoute = $router

						->findRoute( $destination, Route::GET)
					)

						$router->setActiveRoute( $destinationRoute );
					/* Assumptions:
						- this route doesn't care about middlewares, validation etc
						- target route was registered as get request, considering dev will hardly redirect to a post route (cuz they have no payload)
					*/
				}

				elseif ( $destination instanceof Route ) {

					$currViewData = $router

					->setActiveRoute($destination )

					->getPrevRequest()['data'];
				}
			}

			else $router->setActiveRoute($prevReqRoute); // in preparation for below call

			$viewData = $this->routeProvider( $currViewData, $validationErr );

			$router->pushPrevRequest($prevReqRoute, $viewData);

			// $response->publishHtml
		}
	}

?>