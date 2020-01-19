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

		/* @param Array */
		private $contentOptions;

		protected $cachedData;

		private $appContainer;

		/* @param Nmeri\Tilwa\Routes\Route */
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
		* @description: returns a json string containing all info pertaining to the resource given in the name parameter. the encoded arrary returned is made up of a key-value pair of the fields/placeholders in the resource's view
		*
		* @return {String}: json encoded array of saved data, or null otherwise
		*/ 
		public function getContents ( string $name, string $tableName=null ) {

		    $cache = $this->cacheManager();

		    $config = $this->contentOptions;

		    $app = $this->appContainer;

		    // unacceptable cache characters
		    $cachePermitted = substr(preg_replace('/[{}()\/\@:]/', 'INVALID_CHARACTER', $name), 0, 63);

		    $cachedTables = $cache->getItem('allTables');

			$allTables = $cachedTables->get();


		    $cachedRequest = $cache->getItem($cachePermitted);

			$this->cachedData = $cachedRequest->get();
		 

		    if ( !$allTables || !$tableName ) {

				$validTables = $app->connection->prepare("SHOW TABLES WHERE `Tables_in_". getenv('DBNAME')."` NOT LIKE ?");

				$validTables->execute(['contents']);

				$validTables = $validTables->fetchAll(PDO::FETCH_ASSOC);

				$allTables = array_reduce($validTables, function ($a,$b) {
					$x = array_push($a, array_values($b)[0]);

					return $a;
				},[]);

			    $cachedTables->set($allTables)->expiresAfter(60*60*24);

				$cache->save($cachedTables);
			}

			// var_dump( $this->cachedData); //die();
		    if ( !$this->cachedData || !$tableName) {

				$objTable = $cache->getItem('tableFor|'.$cachePermitted);

		    	$tableName = $objTable->get();

		    	// detect table
				if ( !$tableName ) {

					$targetTable = 0;

			    	for ($i=0; $i < count($allTables); $i++) {

						if (!$targetTable && $this->getTableData( $allTables[$i], $name, $cachedRequest ) === true) {

							$targetTable = 1;

							$tableName = $allTables[$i];

							$cache->save($cachedRequest);

						    $objTable->set($tableName)->expiresAfter(60*60*24);

							$cache->save($objTable);
						}
					}
				}

				// set data in cache
				else $this->getTableData( $tableName, $name, $cachedRequest );

				// if cache is still empty, we will assume this is a request from the cms for a view with no data initiated in the database yet
				if (empty($this->cachedData)) {

					$vars = json_decode($this->getFields( ), true);
	    
					$initialVars = [];

					array_walk($vars, function ($el) use (&$initialVars) {
						
						$initialVars[$el] = ''; // init fields with empty strings
					});

					$this->cachedData = json_encode($initialVars);
				}
			}

			//else $cache->deleteItem($name); die(); // for debugging
			// or remove all
			// $cache->clear();
			// var_dump($this->cachedData); die();
			return $this->cachedData;
		}

		/**
	     * populates cachedData for us
	     *
	     * @return {Boolean}: True when a table match is found or false otherwise
	     **/
	    private function getTableData( $tableName, $dataValue, $cachedRequest) {

	    	$config = $this->contentOptions;

	    	$primaryColumns = $config['primaryColumns'] ?? [];

	    	$col = in_array($tableName, array_keys($primaryColumns)) ? $primaryColumns[$tableName] : 'name';

	    	$app = $this->appContainer;


			try {
var_dump('SELECT * FROM `'.$tableName.'` WHERE `'. $col .'`=? OR `'. $col .'`=?');
				$contents = $app->connection->prepare('SELECT * FROM `'.$tableName.'` WHERE `'. $col .'`=? OR `'. $col .'`=?');

				$contents->execute([$dataValue, $this->nameCleanUp($dataValue)]);

				$contents = $contents->fetch(PDO::FETCH_ASSOC);

				if (!empty($contents)) {

					//var_dump($contents, $tableName, $dataValue); die();
					$this->cachedData = json_encode($contents);

				    $cachedRequest->set($this->cachedData)->expiresAfter(120); //in seconds. also accepts Datetime

					return true;
				}
			}
			catch (PDOException $e) {
				return false;
			}
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
		
		public function pairVarToFields ( string $requestedRsx, Route $requestedRoute) {

			$route = $this->route = $requestedRoute;
			
			// get an assoc array of components to be used in the foreach (if it's needed)
			$options = $this->getContentsAsArray( $requestedRsx);

			$app = $this->appContainer;
		

			$vars = $options['dbRow'];

			$vars['universal_date'] = date('Y');
			
			$vars['name'] = $requestedRsx;

			try {
				
				$engine = new TemplateEngine( $this->appContainer, $route );

				// check if repeated components are needed
				$dynamicFields = $engine->fields();
			}
			catch (TypeError $e) {
				
				http_response_code(404); // move this block into the error handler

				$vars['site_name'] = $this->route->requestPath;

				return $errView->parseAll($vars);
			}


			// resolve documents to their native folder
			if (!empty($dynamicFields['foreachs'])) {

				try {

					$additionalVars = $this->routeProvider( $options);
				}
				catch (PDOException $e) {
				
					$log = new Logger('query-input-syntax');

					$log->pushHandler(new StreamHandler($app->rootPath .'logs/404-error.log', Logger::ERROR ));

					// add records to the log
					$log->addError((string) $e);
				}
				
				// REWRITE THESE PARTS
				try {

					if (isset($additionalVars['url_error'])) throw new TypeError("Invalid page", 1);
					
					return $engine->parseAll($vars, $additionalVars);

				} catch (TypeError $e) {
					http_response_code(404);

					return $errView->parseAll(array_merge($vars, $additionalVars));
				}

			}
			
			else return $engine->parseAll($vars);

		}


		/**
		* @description: this can only be used to get most recent information about a resource. it does not return all data about a document (use `getContentsAsArray()` instead). it's intended use is specified for directories/documents that read from other sources that can be updated [and therefore need a live list]
		*
		* @return Array of raw data for plugging into live components
		*/
		protected function routeProvider ( array $options) {

			[$class, $method ]= explode('@', $this->route->source);

			$name = $options['reqResource'];

		    $cache = $this->cacheManager();

		    $qParams = $this->route->queryVars;

		    $nameInStore = $qParams ? preg_replace('/\W/', '_', implode(';', $qParams) ) : $method;


			try	{
				$dataSrc = $this->appContainer->getClass('\\Source\\' .$class);// $this->findDataSource($class);

				$cachedOpts = $cache->getItem(__FUNCTION__.'|'. $nameInStore ); // prefix to avoid clash with other setters

	    		$freshCopy = $cachedOpts->get();


				//if (is_null ($freshCopy)) {

					$freshCopy = $dataSrc->$method( $name, array_merge($options['dbRow'], $qParams));


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

		// direct API call?
		public function getRecentByName ( string $rsxName)	{

			$options = $this->getContentsAsArray( $rsxName);

			return json_encode($this->routeProvider( $options));
		}

		/**
		* @description: identical to `getContents` but for the slight difference in their return values:
		* 	- the latter returns resource's db row
		* 	- this pushes data from `getContents` to key 'dbRow', then adds additional keys
		* @return Array of keys that'll guide `getRecent` in getting fresh data
		*/
		protected function getContentsAsArray ( string $rsxName):array {

			$opts['reqResource'] = $this->nameCleanUp($rsxName);


			$contents = $vars = json_decode($this->getContents( $rsxName), true); // get variables for this temp from db

			if ($contents == 'false') return [];


			$opts['dbRow'] = $vars;

			return $opts;
		}

		// returns a global instance of phpfastcache manager
		public function cacheManager() {

			//Configuring PHP Fast Cache
			CacheManager::setDefaultConfig(new ConfigurationOption([

				"path" =>  $this->appContainer->rootPath ."/req-cache"
			]));

			return CacheManager::getInstance();
		}


		// break up namespace if present, switch to that folder and init the source
		/*private function findDataSource (string $fullName) {

			$container = $this->appContainer;

			if ($exists = $container->getClass($fullName)) return $exists;

			$currDir = getcwd();

			$fulBreak = explode('\\', $fullName);

			$clsName = array_shift($fulBreak);

			$slash = $container->slash;

			$nmspaces = implode($slash, $fulBreak);

			$srcDir = $container->rootPath . 'sources'. $slash. $nmspaces;

			chdir($srcDir);

			if (file_exists( $clsName . '.php') ) {
				
				$clsInst = new $clsName($this->appContainer);

				chdir($currDir); return $clsInst;
			}

			chdir($currDir);
		}*/

		/**
		*
		*@description: options for getting data from db. valid keys are ->

			primaryColumns: Assoc array of primary columns mapped to their table. It will try to get a row where `name` is unique and TERMINATE EXECUTION IF A TABLE WITHOUT `name` or a suppied column exists

			navIndicator: callback given the dataset should return a string that should be outstanding
		*
		*/
		public function getContentOptions ( ):array {

			return [];
		}
	}

?>