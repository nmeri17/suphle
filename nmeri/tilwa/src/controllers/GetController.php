<?php

	namespace Tilwa\Controllers;

	
	use Monolog\Logger;

	use Monolog\Handler\StreamHandler;

	use PDO; use TypeError;

	use Tilwa\Templating\TemplateEngine;

	use Phpfastcache\CacheManager;

	use Phpfastcache\Config\ConfigurationOption;

	use Tilwa\Route\Route;


	class GetController {

		/**
		* @var Array */
		private $contentOptions;

		protected $cachedData;

		/**
		* @var Tilwa\Controllers\Bootstrap */
		private $appContainer;

		/**
		* @var Tilwa\Route\Route */
		private $route;

		function __construct (Bootstrap $app ) {

			$this->contentOptions = $this->getContentOptions();

			$this->appContainer = $app;
		}

	 	// in transit, document names are formatted for url compatibilty. this method aims to reverse it to its original state
	 	public function nameCleanUp ($name) {
	 		
	 		return preg_replace_callback("/-|_|~/", function ($match) {
				
				if ($match[0] == '-') return ' ';

				elseif ($match[0] == '_')  return '-';

				return '_';
			}, $name);
	 	}

	 	// converts a string to a format callable as a method i.e. camelcase or snake cased
	 	public function nameDirty ($name, $dirtMode) {
	 		
	 		return preg_replace_callback('/\b(\s)|(-)(\w)?/', function($a) use ($dirtMode, $name) {

		 		if ($dirtMode == 'dash-case') { // won't have any effect on strings containing underscores

		 			if (!empty($a[1])) return '-';

		 			if (!empty($a[2])) return '_' . $a[3];
		 		}

		 		elseif ($dirtMode == 'camel-case') {

		 			if (!empty($a[3])) return strtoupper($a[3]);
		 		}

		 	}, $name);
	 	}

		
		/**
		* @description: cms helper function. Will NOT throw an error if you attempt to obtain an invalid resource, but will return an array with key url_error
		*
		* @param {retain}: foreach blocks are usually for repeated components, so pass in the names of those you want to edit at the admin panel. They'll appear as single fields. Otherwise they'll all be omitted
		*/
		protected function getFields ( $retain=[]) {

			$fields = [];

			try {
				$engine = new TemplateEngine( $this->appContainer, $this->route );

				$placeholders = $engine->fields();

				unset($placeholders['blockCount']);

				// sieve off repeated components
				array_walk($placeholders, function ($val, $key) use (&$fields, $retain) {
					
					if ($key == 'foreachs') {

						if (!empty($val) && is_array($val)) foreach ($val as $key2 => $repeat) {
						
							if (in_array($repeat, $retain)) $fields[] = $repeat;
						}
						elseif (!empty($val) && !is_array($val)) { // val `description` mysteriously gets here under key `foreach`

							$fields[] = $val;
						}
					}

					else $fields[] = $val;
				});

				return json_encode(array_unique($fields));
			}
			catch(f $e) {

				return json_encode(['url_error' => $this->route->requestPath]);
			}
		}
		
		// assign a couple of default data before setting request on its merry way to a handler method
		public function pairVarToFields ( array $queryPayload ) {

			$container = $this->appContainer;

			$requestedRoute = $container->getActiveRoute();

			$pageVars = compact( 'queryPayload');

			if (!$requestedRoute->source) $viewData = [];

			else $viewData = $this->routeProvider($pageVars );

			$container->setPrevRequest($requestedRoute, $viewData);

			if ($requestedRoute->redirectTo || $requestedRoute->restorePrevVars) // redirection will take precedence over viewless routes

				return $this->changeDestination($requestedRoute, $viewData);

			if ($requestedRoute->viewName === false )

				return json_encode($viewData);

			$engine = new TemplateEngine( $container, $requestedRoute );

			return $engine->parseAll( $viewData );
		}


		/**
		* @description: this can only be used to get most recent information about a resource. it's intended use is specified for directories/documents that read from other sources that can be updated
		*
		* @return Array of raw data for plugging into live components
		*/
		protected function routeProvider ( array $options) {

		    $container = $this->appContainer;

		    $currRoute = $container->getActiveRoute();

			[$class, $method ]= explode('@', $currRoute->source);

			$qParams = $options['queryPayload'];

		    $cache = $this->cacheManager();

		    $nameInStore = $qParams ? preg_replace('/\W/', '_', implode(';', $qParams) ) : $method;

			try	{
				$dataSrc = $container->getClass('\\' . $container->sourceNamespace .'\\' .$class);

				$cachedOpts = $cache->getItem(__FUNCTION__.'|'. $nameInStore ); // prefix to avoid clash with other setters
	    		$freshCopy = $cachedOpts->get();

				//if (is_null ($freshCopy)) {

					$freshCopy = $dataSrc->$method( $qParams, $currRoute->parameters);

	    			$cachedOpts->set($freshCopy)->expiresAfter(60*10);

	    			$cache->save($cachedOpts);
				//}

				return $freshCopy;
			}

			// no suitable handler
			catch (TypeError $e) {
				
				$log = new Logger('404-error');

				$log->pushHandler(new StreamHandler($container->rootPath .'logs/404-error.log', Logger::ERROR ));

				// add records to the log
				$log->error($e);
		    	return ['url_error' => '"' .$currRoute->requestSlug . '"'];
			}

			catch (Error $e) {
			
				$log = new Logger('404-error');

				$log->pushHandler(new StreamHandler($container->rootPath .'logs/404-error.log', Logger::ERROR ));

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

		/**
		*@description: semantic options after getting data from db. valid keys are ->
			navIndicator: callback given the dataset should return a string that should be outstanding
		*/
		public function getContentOptions ( ):array {

			return [];
		}

		// if it's 'reload', replace current route with that matching user previous request. then merge the view data with what we have now
		private function changeDestination (Route $route, array $currViewData) {

			$app = $this->appContainer;

			if (!$route->restorePrevVars)

				return header('Location: '. $route->redirectTo($currViewData));

			$prevReq = $app->getPrevRequest();

			$prevReqRoute = $prevReq['route'];

			$newRoute = $app->router->findRoute($prevReqRoute->requestSlug, $prevReqRoute->method);

			$engine = new TemplateEngine( $app, $newRoute );

			return $engine->parseAll( $currViewData + $prevReq['data'] );
		}
	}

?>