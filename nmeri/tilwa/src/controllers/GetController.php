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
		public function pairVarToFields ( string $resourceName, Route $requestedRoute) {

			$this->route = $requestedRoute;
			
			$pageVars = compact('resourceName');

			$engine = new TemplateEngine( $this->appContainer, $requestedRoute, $pageVars);

			return $engine->parseAll( $this->routeProvider($pageVars) );
		}


		/**
		* @description: this can only be used to get most recent information about a resource. it's intended use is specified for directories/documents that read from other sources that can be updated
		*
		* @return Array of raw data for plugging into live components
		*/
		protected function routeProvider ( array $options) {

			[$class, $method ]= explode('@', $this->route->source);

			$name = $options['resourceName'];

		    $cache = $this->cacheManager();

		    $qParams = $this->route->queryVars;

		    $nameInStore = $qParams ? preg_replace('/\W/', '_', implode(';', $qParams) ) : $method;

		    $container = $this->appContainer;

			try	{
				$dataSrc = $container->getClass('\\' . $container->sourceNamespace .'\\' .$class);

				$cachedOpts = $cache->getItem(__FUNCTION__.'|'. $nameInStore ); // prefix to avoid clash with other setters
	    		$freshCopy = $cachedOpts->get();

				//if (is_null ($freshCopy)) {

					$freshCopy = $dataSrc->$method( $name, $qParams);

	    			$cachedOpts->set($freshCopy)->expiresAfter(60*10);

	    			$cache->save($cachedOpts);
				//}

				return $freshCopy;
			}

			// no suitable handler
			catch (TypeError $e) {
				
				$log = new Logger('404-error');

				$log->pushHandler(new StreamHandler($app->rootPath .'logs/404-error.log', Logger::ERROR ));

				// add records to the log
				$log->addError((string) $e);
		    	return ['url_error' => '"' .$this->route->requestPath . '"'];
			}

			catch (Error $e) {
			
				$log = new Logger('404-error');

				$log->pushHandler(new StreamHandler($app->rootPath .'logs/404-error.log', Logger::ERROR ));

				$log->addError((string) $e);
				return ['url_error' => '"' . $this->route->requestPath. '"'];
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
		*@description: options for getting data from db. valid keys are ->
			navIndicator: callback given the dataset should return a string that should be outstanding
		*/
		public function getContentOptions ( ):array {

			return [];
		}
	}

?>