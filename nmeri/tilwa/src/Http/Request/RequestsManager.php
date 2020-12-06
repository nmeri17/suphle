<?php

	namespace Tilwa\Request\Http;

	
	use Phpfastcache\CacheManager;

	use Phpfastcache\Config\ConfigurationOption;

	use Tilwa\Route\Route;

	use Monolog\Logger;

	use Monolog\Handler\StreamHandler;


	class RequestsManager {

		protected $cachedData;

		/**
		* @var Tilwa\Controllers\Bootstrap */
		private $appContainer;

		/**
		* @var Tilwa\Route\Route */
		private $route;

		/**
		* @var bool */
		private $failedValidation;

		/**
		* @var object */
		private $dataSource;

		function __construct (Bootstrap $app, array $requestParameters ) {

			$this->appContainer = $app;

			$this->insertPayload($requestParameters);
		}
		
		// assign a couple of default data before setting request on its merry way to a handler method
		private function insertPayload ( array $parameters ) {

			$container = $this->appContainer;

			$router = $container->router;

			$requestedRoute = $router->getActiveRoute();

			$validationErr = [];

			if (!$requestedRoute->source) $viewData = [];

			else $viewData = $this->routeProvider($queryPayload );

			if ($this->failedValidation)

				[$viewData, $validationErr] = $viewData; // replace whatever data was stored in previous request with current payload
			$router->pushPrevRequest($requestedRoute, $viewData);
			
			// from here downward should be moved to the response manager
			// afterwards, update response fetching in front controller
			// alternatively, move this to root of http namespace and move from here downward into its own method
			if (
				!empty($validationErr) ||

				!is_null($requestedRoute->getRedirectDestination())
			) { // redirection will take precedence over viewless routes
				// this flow needs to change if we're breaking down the routes into separate classes

				if ($this->failedValidation)

					$requestedRoute->restorePrevPage = true;

				return $this->changeDestination($requestedRoute, $viewData, $validationErr);
			}
		}

		/**
		* @description: after validating request payload, find route's handler. If validation passes, call handler, passing the query (where present) and request payload. on failure, previous payload will be overridden by the bad data (in the app request memory). the previous route will equally be called with this bad data
		*
		* @return Array of raw data for plugging into components
		*/
		protected function routeProvider ( array $queryPayload, array $validationResp = []) {

		    $container = $this->appContainer;

		    $currRoute = $container->router->getActiveRoute();

			[$class, $method ]= explode('@', $currRoute->source);

			// $cache = $this->cacheManager();

		    $nameInStore = $queryPayload ? preg_replace('/\W/', '_', implode(';', $queryPayload) ) : $method;

			try	{
				
				$this->dataSource = $dataSrc = $container->getClass('\\' . $container->sourceNamespace .'\\' .$class); // TODO: Plug in the model name here

				$validator = @$dataSrc->validator;

				/*$cachedOpts = $cache->getItem(__FUNCTION__.'|'. $nameInStore ); // prefix to avoid clash with other setters
	    		$freshCopy = $cachedOpts->get();

				if (is_null ($freshCopy)) {*/

	    			if (isset($validator) && method_exists($validator, $method)) {

	    				$validationResp = $container->getClass($validator)
						->$method( $queryPayload, $currRoute->parameters);

	    				if (!empty($validationResp)) {

	    					$this->failedValidation = true;

	    					return [$queryPayload, $validationResp];
	    				}
	    			}

					$freshCopy = $dataSrc->$method( $queryPayload, $currRoute->parameters, $validationResp);

	    			/*$cachedOpts->set($freshCopy)->expiresAfter(60*10);

	    			$cache->save($cachedOpts);
				}*/

				return $freshCopy;
			}

			catch (\Exception $e) { // review this block
			
				$log = new Logger('500-error');

				$log->pushHandler(new StreamHandler($container->rootPath .'logs/500-error.log', Logger::ERROR ));

				$log->error($e);
				return ['url_error' => '"' . $currRoute->requestSlug. '"'];
			}
		}

		// returns a global instance of phpfastcache manager
		public function cacheManager() {

			//Configuring PHP Fast Cache
			CacheManager::setDefaultConfig(new ConfigurationOption([

				"path" =>  $this->appContainer->rootPath ."/req-cache"
			]));

			return CacheManager::getInstance();
		}

		// if it's 'reload', replace current route with that matching user previous request. then merge the view data with what we have now
		private function changeDestination (Route $route, array $currViewData, array $validationErr) {

			$app = $this->appContainer;

			$router = $app->router;

			$prevReqRoute = $router->getPrevRequest()['route'];

			if (is_null($prevReqRoute)) { // currently the 1st route

				var_dump($_SESSION['prev_requests'], $route );

				$prevReqRoute = $route;
			}

			if (!$route->restorePrevPage && !$this->failedValidation) {

				$destinationCallback = $route->getRedirectDestination();

				$destination = $destinationCallback($currViewData, function ($defaultRoute) {

					return $this->appContainer->router->hinderedRequest($defaultRoute);
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