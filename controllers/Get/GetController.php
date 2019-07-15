<?php

	namespace Get;

	
	use \Monolog\Logger;

	use \Monolog\Handler\StreamHandler;

	use \PDO; use \TypeError;

	use \Model;

	use \Templating\TemplateEngine;

	use \Phpfastcache\CacheManager;

	use \Phpfastcache\Config\ConfigurationOption;

/**
* @description: This class is made up of regular and specific-purpose methods.

-    Endpoints to be parsed automatically pass through this class's pairVarToFields. That method calls 'regular' methods who handle the nitty gritty of getting data and template for the requested string.
-    'Specific-purpose' methods exist for the purpose of performing operations that return raw data with no intention of being parsed against a view.
*/
class GetController {

 	// document names are formatted for url compatibilty. this method aims to reverse it to its original state
 	public static function nameCleanUp ($name) {
 		
 		return preg_replace_callback("/-|_|~/", function ($match) {
			
			if ($match[0] == '-') return ' ';

			elseif ($match[0] == '_')  return '-';

			return '_';
		}, $name);
 	}

 	public static function nameDirty ($name, $dirtMode) {
 		
 		return preg_replace_callback('/\b(\s)|(-)(\w)?/', function($a) use ($dirtMode, $name) {

	 		if ($dirtMode == 'dash-case') {

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
	* @param {config}: options for getting data from db. valid keys are ->

		primaryColumns: Assoc array of primary columns mapped to their table. It will try to get a row where `name` is unique and TERMINATE EXECUTION IF A TABLE WITHOUT `name` or a suppied column exists

		setNavIndicator: callback given the dataset should return a key-value pair for the nav to set a value your views can understand. default behaviour maps view name to nav_indicator column
	*
	* @return {String}: json encoded array of saved data or null otherwise
	*/ 
	static function getContents (PDO $conn, string $name, $config=[]) {

	    $cache = self::cacheManager();

	    // unacceptable cache characters
	    $cachePermitted = substr(preg_replace('/[{}()\/\@:]/', 'INVALID_CHARACTER', $name), 0, 63);

	    $cachedTables = $cache->getItem('allTables');

		$allTables = $cachedTables->get();


	    $cachedRequest = $cache->getItem($cachePermitted);

		$cachedData = $cachedRequest->get();

		/**
	     * populates cachedData for us
	     *
	     * @return {Boolean}: True when a table match is found or false otherwise
	     **/
	    $getTablData = function ($conn, $tableName, $dataValue) use (&$cachedData, $cachedRequest, $config) {

	    	$primaryColumns = $config['primaryColumns'] ?? [];

	    	$col = in_array($tableName, array_keys($primaryColumns)) ? $primaryColumns[$tableName] : 'name';


			try {

				$contents = $conn->prepare('SELECT * FROM `'.$tableName.'` WHERE `'. $col .'`=?');

				$contents->execute([$dataValue]);

				$contents = $contents->fetch(PDO::FETCH_ASSOC);

				if (!empty($contents)) {

					if ($tableName == 'page') {

						if (isset($config['setNavIndicator']) && is_callable($config['setNavIndicator'])) {

							foreach ($config['setNavIndicator']($contents) as $key => $value) $contents[$key] = $value;
						}

						else {

							$navName = 'active_'. self::nameDirty($contents['name'], 'dash-case');

							$contents[preg_replace('/\s+/', '_', $navName)] = $contents['nav_indicator'];
						}

						$contents['type'] = $contents['name'];

						unset($contents['nav_indicator']);
					}
//var_dump($contents, $tableName, $dataValue); die();
					$cachedData = json_encode($contents);

				    $cachedRequest->set($cachedData)->expiresAfter(120); //in seconds, also accepts Datetime

					return true;
				}
			}
			catch (PDOException $e) {
				return false;
			}
		};
	 

	    if (is_null($allTables)) {

			$validTables = $conn->prepare("SHOW TABLES WHERE `Tables_in_` NOT LIKE ?");

			$validTables->execute(['contents']);

			$validTables = $validTables->fetchAll(PDO::FETCH_ASSOC);

			$allTables = array_reduce($validTables, function ($a,$b) {
				$x = array_push($a, array_values($b)[0]);

				return $a;
			},[]);

		    $cachedTables->set($allTables)->expiresAfter(60*60*24);

			$cache->save($cachedTables);
		}
// var_dump( $cachedData); //die();
	    if (is_null($cachedData)) {

			$objTable = $cache->getItem('tableFor|'.$cachePermitted);

	    	$tableName = $objTable->get();

	    	// detect table
			if (is_null($tableName)) {

				$targetTable = 0;

		    	for ($i=0; $i < count($allTables); $i++) {

					if (!$targetTable && $getTablData($conn, $allTables[$i], $name) === true) {

						$targetTable = 1;

						$tableName = $allTables[$i];

						$cache->save($cachedRequest);

					    $objTable->set($tableName)->expiresAfter(60*60*24);

						$cache->save($objTable);
					}
				}
			}

			// set data in cache
			else $getTablData($conn, $tableName, $name);

			// if cache is still empty, `name` is either invalid or a this is a request from the cms for a view with no data initiated in the database yet
			if (empty($cachedData)) {

				if ($tableName == 'page' && file_exists("../views/$name.tmpl")) {
				    
				    $vars = json_decode(TilwaGet::getFields($name), true);
    
    				$initialVars = [];
    
    				array_walk($vars, function ($el) use (&$initialVars) {
    					
    					$initialVars[$el] = '';
    				});
    
    				$cachedData = json_encode($initialVars);
				}
				else $cachedData = null;
			}
		}

		//else $cache->deleteItem($name); die(); // for debugging
		// or remove all
		// $cache->clear();
// var_dump($cachedData); die();
		return $cachedData;
	}	

	
	/**
	* @description: cms helper function. Will NOT throw an error if you attempt to obtain an invalid resource, but will return an array with key url_error
	*
	* @param {dummy}: shoved down from child methods because PHP doesn't support method overloading
	* @param {retain}: foreach blocks are usually for repeated components, so pass in the names of those you want to edit at the admin panel. They'll appear as single fields. Otherwise they'll all be omitted
	*/
	static function getFields (PDO $dummy, string $rsx, $retain=[]) {

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
	
	public static function pairVarToFields ($conn, $tempName) {
		
		// get an assoc array of components to be used in the foreach (if it's needed)
		$options = TilwaGet::getContentsAsArray($conn, $tempName);
	

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

			$vars['site_name'] = explode('/', $_SERVER['REQUEST_URI']) [1];

			return $engine->parseAll($vars);
		}


		// resolve documents to their native folder
		if (!empty($dynamicFields['foreachs'])) {

			try {

				$additionalVars = self::getRecentByOpts($conn, $options);
			}
			catch (PDOException $e) {
			
				$log = new Logger('query-input-syntax');

				$log->pushHandler(new StreamHandler(__DIR__.'/404-error.log', Logger::ERROR ));

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
	* @description: this can only be used to get most recent information about a resource. it does not return all data about a document (use `getContentsAsArray` instead). it's intended use is specified for directories/documents that read from other sources that can be updated [and therefore need a live list]
	*
	* @return Array of raw data for plugging into live components
	*/
	public static function getRecentByOpts (PDO $conn, array $options) {

		$handler= self::nameDirty($options['handler'], 'camel-case');

		$name = $options['for'];

	    $cache = self::cacheManager();


		try	{
			preg_match('/([\w=&,-:]+)$/', @urldecode($_GET['query']), $viewState);


			if (!empty($viewState)) {

	    		$cachedOpts = $cache->getItem(__FUNCTION__.'|'.preg_replace('/\W/', '_', $viewState[1]));

	    		$freshCopy = $cachedOpts->get();


				//if (is_null ($freshCopy)) {

					parse_str($viewState[1], $opts);

					$freshCopy = Model::$handler($conn, $name, array_merge($options['oldVars'], $opts));


	    			$cachedOpts->set($freshCopy)->expiresAfter(60*10);

	    			$cache->save($cachedOpts);
				//}

				return $freshCopy;
			}

			/* this should run if 
				*it's a valid document and isn't a subcategory i.e. sermons under "blog posts" handler
				*it's a single page that requires live foreachs
			*/
			if ( method_exists('Model', $handler)) {

	    		$cachedPage = $cache->getItem(__FUNCTION__."|$handler"); // prefix to avoid clash with other setters

	    		$freshCopy = $cachedPage->get();

	    		// if (is_null ($freshCopy)) {

	    			$freshCopy = Model::$handler($conn, $name, $options['oldVars']);

	    			$cachedPage->set($freshCopy)->expiresAfter(60*5);

	    			$cache->save($cachedPage);
	    		// }

	    		return $freshCopy;
	    	}

			throw new TypeError('no suitable handler', 1);
		}

		catch (TypeError $e) {
			
			$log = new Logger('404-error');

			$log->pushHandler(new StreamHandler(__DIR__.'/404-error.log', Logger::ERROR ));

			// add records to the log
			$log->addError((string) $e);
	    	return ['url_error' => '"' .explode('/', $_SERVER['REQUEST_URI'])[1] . '"']; // in production, change index to `1`
		}

		catch (Error $e) {
		
			$log = new Logger('404-error');

			$log->pushHandler(new StreamHandler(__DIR__.'/404-error.log', Logger::ERROR ));

			// add records to the log
			$log->addError((string) $e);
			return ['url_error' => '"' .explode('/', $_SERVER['REQUEST_URI'])[2] . '"'];
		}
	}

	public static function getRecentByName (PDO $conn, string $rsxName)	{

		$options = TilwaGet::getContentsAsArray($conn, $rsxName);

		return json_encode(self::getRecentByOpts($conn, $options));
	}

	/**
	* @description: internal method identical to `getContents` but for the slight difference in their return values:
	* 	- the latter returns a JSON string of static data
	* 	- this pushes the return value of the latter to key 'oldVars' and adds additional keys
	* @return Array of keys that'll guide `getRecent` in getting fresh data
	*/
	private static function getContentsAsArray (PDO $conn, string $rsxName):array {

		$name = self::nameCleanUp($rsxName);


		$contents = $vars = json_decode(TilwaGet::getContents($conn, $rsxName), true); // get variables for this temp from db

		if ($contents == 'false') return [];

		$vars = $vars == 'false' ? [] : $vars;


		$opts['handler'] = @$contents['view-name']; // @ in case invalid resource request

		$opts['for'] = $name;

		$opts['oldVars'] = $vars;

		return $opts;
	}


	public static function search ($conn, $toSearch) {

		$toSearch = self::nameCleanUp($toSearch);

		$like = "%$toSearch%";

		$conn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false); // to retain int data type
		
		// this table has been deprecated. use getContents instead
		$searchRes = $conn->prepare('SELECT name, variables FROM contents WHERE `type`= ? AND `name` LIKE ? LIMIT ?');

		$searchRes->execute(['cart', $like, 10]);


		$searchRes = array_map(function ($arr) {

			$dir = json_decode($arr, true)['type'];

			return "<a href=/dig-currency/$dir/". self::nameDirty($arr['name'], 'dash-case') . '> ' . $arr['name'] . '</a>';

		}, $searchRes->fetchAll(PDO::FETCH_ASSOC));

		if (empty($searchRes)) $searchRes = ['no result found'];

		return json_encode($searchRes);
	}

	/**
	* @desciption: some podcasts are uploaded several days in advance but only to be displayed on their due date. this method returns all podcasts whose due date is in the future
	*
	**/
	public static function futurePosts ($conn, $address) {

		// return json_encode(Model::afterToday($conn, $address, 'date', NULL));
	}

	public static function lazyLoad ($conn, $url) {
		# invokes `key` method in `Model` with the $_GET keys `curr` {i+$curr} as parameter `opts`
	}

	// returns a global instance of phpfastcache manager
	public static function cacheManager() {

		//Configuring PHP Fast Cache
		CacheManager::setDefaultConfig(new ConfigurationOption([

			"path" =>  dirname(__FILE__)."/files"
		]));

		return CacheManager::getInstance();
	}
}
?>