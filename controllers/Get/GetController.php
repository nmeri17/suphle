<?php

	namespace Get;

	
	use Monolog\Logger;

	use Monolog\Handler\StreamHandler;

	use PDO; use TypeError;

	use Templating\TemplateEngine;

	use Phpfastcache\CacheManager;

	use Phpfastcache\Config\ConfigurationOption;

/**
* @description: This class is made up of regular and specific-purpose methods.

-    Endpoints to be parsed automatically pass through this class's pairVarToFields. That method calls 'regular' methods who handle the nitty gritty of getting data and template for the requested string.
-    'Specific-purpose' methods exist for the purpose of performing operations that return raw data with no intention of being parsed against a view.
*/
class GetController {

	const PATH_INDEX = getenv('ENV') == 'dev' ? 1 : 2;

	protected $controller;

	private $contentOptions;

	protected $cachedData;

	function __construct (PDO $conn ) {

		$this->contentOptions = $this->getContentOptions() ?? [];

		$this->connection = $conn;
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
	protected function getContents ( string $name ) {

	    $cache = $this->cacheManager();

	    $config = $this->contentOptions;

	    // unacceptable cache characters
	    $cachePermitted = substr(preg_replace('/[{}()\/\@:]/', 'INVALID_CHARACTER', $name), 0, 63);

	    $cachedTables = $cache->getItem('allTables');

		$allTables = $cachedTables->get();


	    $cachedRequest = $cache->getItem($cachePermitted);

		$this->cachedData = $cachedRequest->get();
	 

	    if (is_null($allTables)) {

			$validTables = $this->connection->prepare("SHOW TABLES WHERE `Tables_in_". getenv('DBNAME')."` NOT LIKE ?");

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
	    if (is_null($this->cachedData)) {

			$objTable = $cache->getItem('tableFor|'.$cachePermitted);

	    	$tableName = $objTable->get();

	    	// detect table
			if (is_null($tableName)) {

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

			// if cache is still empty, `name` is either invalid or a this is a request from the cms for a view with no data initiated in the database yet
			if (empty($this->cachedData)) {

				if ($tableName == 'page' && file_exists(APP_ROOT . "views/$name.tmpl")) {
				    
				    $vars = json_decode($this->getFields($name), true);
    
    				$initialVars = [];
    
    				array_walk($vars, function ($el) use (&$initialVars) {
    					
    					$initialVars[$el] = '';
    				});
    
    				$this->cachedData = json_encode($initialVars);
				}
				else $this->cachedData = null;
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


		try {

			$contents = $this->connection->prepare('SELECT * FROM `'.$tableName.'` WHERE `'. $col .'`=? OR `'. $col .'`=?');

			$contents->execute([$dataValue, $this->nameCleanUp($dataValue)]);

			$contents = $contents->fetch(PDO::FETCH_ASSOC);

			if (!empty($contents)) {

				if ($tableName == 'page') {

					if (isset($config['setNavIndicator']) && is_callable($config['setNavIndicator'])) {

						foreach ($config['setNavIndicator']($contents) as $key => $value) $contents[$key] = $value;
					}

					else {

						$navName = 'active_'. $this->nameDirty($contents['name'], 'dash-case');

						$contents[preg_replace('/\s+/', '_', $navName)] = $contents['nav_indicator'];
					}

					$contents['type'] = $contents['name'];

					unset($contents['nav_indicator']);
				}
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
	private function getFields ( string $rsx, $retain=[]) {

		$fields = [];

		try {
			$engine = new TemplateEngine($rsx);

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

			return json_encode(['url_error' => $rsx]);
		}
	}
	
	protected function pairVarToFields ( $tempName) {
		
		// get an assoc array of components to be used in the foreach (if it's needed)
		$options = $this->getContentsAsArray( $tempName);
	

		$type = $options['handler'] ?? 'error';

		$vars = $options['oldVars'];

		$vars['universal_date'] = date('Y');
		
		$vars['type'] = $type;
		
		$vars['name'] = $tempName; // should either be name of the requested resource or the name its row is stored with

		try {
			
			if ($type == 'error') throw new TypeError("Error Processing Request", 1);

			$dynamicFields = new TemplateEngine($type);

			// check if repeated components are needed
			$dynamicFields = $dynamicFields->fields();
		}
		catch (TypeError $e) {
			
			http_response_code(404);

			$engine = new TemplateEngine('error');

			$vars['site_name'] = explode('/', $_SERVER['REQUEST_URI']) [self::PATH_INDEX];

			return $engine->parseAll($vars);
		}


		// resolve documents to their native folder
		if (!empty($dynamicFields['foreachs'])) {

			try {

				/*$frontInstance = APP_ROOT . 'controllers' . DIRECTORY_SEPARATOR . __CLASS__;

				if ( method_exists( $frontInstance, 'routeProvider') )
					
					$additionalVars = call_user_func([$frontInstance, 'routeProvider', $options]);

				else */$additionalVars = $this->routeProvider( $options);
			}
			catch (PDOException $e) {
			
				$log = new Logger('query-input-syntax');

				$log->pushHandler(new StreamHandler(APP_ROOT.'logs/404-error.log', Logger::ERROR ));

				// add records to the log
				$log->addError((string) $e);
			}
			

			try {

				if (isset($additionalVars['url_error'])) throw new TypeError("Invalid page", 1);
				
				$engine = new TemplateEngine($type);

				return $engine->parseAll($vars, $additionalVars);

			} catch (TypeError $e) {
				http_response_code(404);

				$engine = new TemplateEngine('error');

				return $engine->parseAll(array_merge($vars, $additionalVars));
			}

		}
		
		else {

			try {

				$engine = new TemplateEngine($type);

				return $engine->parseAll($vars);
			}
			catch (TypeError $e) { var_dump('else3'); die();

				$engine = new TemplateEngine('error');

				return $engine->parseAll($vars);
			}
		}

	}


	/**
	* @description: this can only be used to get most recent information about a resource. it does not return all data about a document (use `getContentsAsArray()` instead). it's intended use is specified for directories/documents that read from other sources that can be updated [and therefore need a live list]
	*
	* @return Array of raw data for plugging into live components
	*/
	protected function routeProvider ( array $options) {

		$handler= $this->nameDirty($options['handler'], 'camel-case');

		$name = $options['for'];

	    $cache = $this->cacheManager();


		try	{
			preg_match('/([\w=&,-:]+)$/', @urldecode($_GET['query']), $viewState);

			$dataSrc = {new $this->findDataSource($handler)} ( $this->connection ); // consider a small store for this in case the need arises to glean more than one source at a time

			if (!empty($viewState)) {

	    		$cachedOpts = $cache->getItem(__FUNCTION__.'|'.preg_replace('/\W/', '_', $viewState[1]));

	    		$freshCopy = $cachedOpts->get();


				//if (is_null ($freshCopy)) {

					parse_str($viewState[1], $opts);

					$freshCopy = $dataSrc->$handler( $name, array_merge($options['oldVars'], $opts));


	    			$cachedOpts->set($freshCopy)->expiresAfter(60*10);

	    			$cache->save($cachedOpts);
				//}

				return $freshCopy;
			}

			/* this will run if 
				*it's a valid document and isn't a subcategory i.e. sermons under "blog posts" handler
				*it's a single page that requires live foreachs
			*/
			$cachedPage = $cache->getItem(__FUNCTION__."|$handler"); // prefix to avoid clash with other setters

    		$freshCopy = $cachedPage->get();

    		// if (is_null ($freshCopy)) {

    			$freshCopy = $dataSrc->$handler( $name, $options['oldVars']);

    			$cachedPage->set($freshCopy)->expiresAfter(60*5);

    			$cache->save($cachedPage);
    		// }

    		return $freshCopy;
		}

		// no suitable handler
		catch (TypeError $e) {
			
			$log = new Logger('404-error');

			$log->pushHandler(new StreamHandler(APP_ROOT.'logs/404-error.log', Logger::ERROR ));

			// add records to the log
			$log->addError((string) $e);
	    	return ['url_error' => '"' .explode('/', $_SERVER['REQUEST_URI'])[ self::PATH_INDEX] . '"'];
		}

		catch (Error $e) {
		
			$log = new Logger('404-error');

			$log->pushHandler(new StreamHandler(APP_ROOT.'logs/404-error.log', Logger::ERROR ));

			// add records to the log
			$log->addError((string) $e);
			return ['url_error' => '"' .explode('/', $_SERVER['REQUEST_URI'])[ self::PATH_INDEX ] . '"'];
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
	* 	- this pushes data from `getContents` to key 'oldVars', then adds additional keys
	* @return Array of keys that'll guide `getRecent` in getting fresh data
	*/
	private function getContentsAsArray ( string $rsxName):array {

		$name = $this->nameCleanUp($rsxName);


		$contents = $vars = json_decode($this->getContents( $rsxName), true); // get variables for this temp from db

		if ($contents == 'false') return [];

		$vars = $vars == 'false' ? [] : $vars;


		$opts['handler'] = @$contents['view-name']; // @ in case invalid resource request

		$opts['for'] = $name;

		$opts['oldVars'] = $vars;

		return $opts;
	}

	// returns a global instance of phpfastcache manager
	public function cacheManager() {

		//Configuring PHP Fast Cache
		CacheManager::setDefaultConfig(new ConfigurationOption([

			"path" =>  APP_ROOT ."/req-cache"
		]));

		return CacheManager::getInstance();
	}

	private function findDataSource ($action) {

		$currDir = getcwd(); $srcDir = APP_ROOT . 'sources'; chdir($srcDir);

		foreach (glob($srcDir . DIRECTORY_SEPARATOR .'*.php') as $fName) {
			
			$src = substr($fName, 0, strlen($fName)-4);

			if (method_exists($src, $action)) {

				chdir($currDir); return new $src;
			}
		}

		chdir($currDir);
	}

	/**
	*
	*@description: options for getting data from db. valid keys are ->

		primaryColumns: Assoc array of primary columns mapped to their table. It will try to get a row where `name` is unique and TERMINATE EXECUTION IF A TABLE WITHOUT `name` or a suppied column exists

		setNavIndicator: callback given the dataset should return a key-value pair for the nav to set a value your views can understand. default behaviour maps view name to nav_indicator column
	*
	*/
	protected function getContentOptions ( string $rsxName ):array {}
}
?>