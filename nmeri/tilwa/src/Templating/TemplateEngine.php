<?php

namespace Tilwa\Templating;

use Tilwa\Controllers\{Bootstrap, GetController};

use Tilwa\Sources\BaseSource;


class TemplateEngine {
	
	private $regex;

	private $forEachRegex;

	private $fileComponent;

	public $file;

	private $staticVars;

	private $appContainer;

	private $sourceClass;

	private $viewData;
	

	function __construct(Bootstrap $app, BaseSource $source, array $dataToParse ) {

		$this->appContainer = $app;

		$this->sourceClass = $source;

		$this->viewData = $dataToParse;

		$this->setNavActive();

		$this->assignFile();

		$this->setRegex();
	}


	/**
	 * undocumented function
	 *
	 * @return 1D array of every field in a traversable component
	 **/
	public function fields () {

		# get all text between __foreach-*__ and exclude them from the string being searched

		// foreachs return a numeric array of all foreachs on this page
		$forEach = array('foreachs' => [], 'blockCount' => 0);


		if (!empty($this->file)) {
			$otherFields = preg_replace_callback($this->forEachRegex, function ($match) use (&$forEach) {
				# log match and return empty string

				preg_match_all($this->regex, $match[1], $log);

				$forEach['foreachs'][] = $log[1];

			}, $this->file);

			
			$forEach['blockCount'] = count($forEach['foreachs']);


			preg_match_all($this->regex, $otherFields, $returnArr);

			// flatten all
			$forEach['foreachs'] = array_reduce($forEach['foreachs'], function($a, $b) {
				
				return array_merge($a, array_values($b));
			}, []);

			return array_merge($returnArr[1], $forEach);
		}
		return [];
	}

	// if it finds no single variables to parse in this context, it'll return an empty string
	private function matchRecursive (array $dataSet, string $context) {

		// block level to loop over
		preg_match('/{{__foreach-(\d)-start__}}/', $context, $initD);

		if (!empty($initD)) $dimension = +$initD[1];

		else $dimension = 2; // 1 is peeled off by default

		

		$carry = ''; $that = $this;

		$tempContext = $context;


		foreach ($dataSet as $key => $value) {

			$rowsInThisComp = ''; $oldDS = [];

			if (is_array($value)) {
			
				$header = substr($context, 0, strpos($context, '{{__foreach-'. $dimension.'-start__}}'));
			 		
			 	preg_match_all('/{{__foreach-'. $dimension.'-start__}}(.+?){{__foreach-'. $dimension.'-end__}}/s', $context, $nestedComponent); // the 's' flag causes . to match every char, including whitespace

		 		if (!empty($header) || !empty($nestedComponent[1])) {

		 			if (!empty($header) ) {

		 				preg_match($this->regex, $header, $headerName);

			 			// is there a nested dataSet
			 			if (!empty($headerName)) {

			 				$hn = $headerName[1];

			 				if (!array_key_exists($hn, $dataSet)) {

				 				$oldDS = $dataSet; // in case we have need for it tomorrow

				 				$dataSet = $this->dSetSearch($hn, $dataSet);
							}

							$rowHeader = $this->matchRecursive([$hn => $dataSet[$hn]], $header);

							// headerName is equally a key in the parent dataSet so prevent it from overwriting parsed values later on					
							unset($dataSet[$hn]);
			 			}
			 			
			 			else $rowHeader = $this->matchRecursive($dataSet, $header);
			 		}
			 		else $rowHeader = $header;

		 			
		 			$fullMatch = array_shift($nestedComponent); // can't pass ref

					$fullMatch = end($fullMatch);

					$rowFooter ='';
		 			

	 				$pairIndex = is_int($key) ? $key: array_search($key, array_keys($dataSet));

	 				$currCompo = $nestedComponent[0][$pairIndex];

					// capture text inbetween components but excluding our own header
					preg_match('/{{__foreach-'. $dimension .'-end__}}(.*)(?:{{__foreach-'. $dimension .'-start__}})'. preg_quote($currCompo, '/'). '/s', $context, $txtBtwSibling);

	 				if ( isset($txtBtwSibling[1])) $rowsInThisComp .= $txtBtwSibling[1];

		 			preg_match('/{{__foreach-'. $dimension .'-start__}}(.+?){{__foreach-'. strval($dimension+1).'-start__}}/s', $context, $ctxHeader);

	 				foreach ($value as $key2 => $value2) {

	 					// no string placeholders allowed within nested components
	 					if (is_array($value2)) {
 			
	 						preg_match_all('/{{__foreach-'. strval($dimension+1).'-start__}}(.+?){{__foreach-'. strval($dimension+1).'-end__}}/s', $currCompo, $isPreggo); // all need to come with the previous end. then, the first needs to go with dimension without the tag+1

	 						// multiple grouped nested templates?
	 						if (!empty($isPreggo[0])) {

	 							// keys hanging within subgroups
	 							if (!empty($fullMatch) && isset($value[$key])) {

 									$singleK = array_filter($value[$key], function($val) { return !is_array($val);});

 									$tCtx =$this->matchRecursive($singleK, $context);

 									if (!empty($tCtx)) $context = $tCtx;
	 							}

	 							// attach header and footer
	 							if (count($isPreggo[0]) == 1) $rowsInThisComp .= $this->aggregateOrSingle($value2, $isPreggo[0][0], ['node'=>0, 'header'=> $ctxHeader[1]]);

	 							else foreach ($isPreggo[0] as $key3 => $value3) {

	 								// valid data set and corresponding nested template
	 								if ( isset($value2[$key3]) && $key== $key2) {// review the necessity of this restraint. don't we, instead, need pairedIndex2?

			 							if ($key3 == 0) {

		 									preg_match('/{{__foreach-'. $dimension .'-start__}}(.+?){{__foreach-'. strval($dimension+1).'-start__}}/s', $context, $ctxHeader);

		 									$rowsInThisComp .= $ctxHeader[1] . $this->matchRecursive($value2[$key3], $value3);
		 								}

			 							else {
			 								
			 								preg_match('/{{__foreach-'. strval($dimension+1) .'-end__}}(?:([\s\S]+?)'. preg_quote($value3, '/'). ')/', $currCompo, $btwTxt);

			 								$rowsInThisComp .= @$btwTxt[1]. $this->matchRecursive($value2[$key3], $value3);
			 							}		
	 								}
	 							}
	 						}

	 						// single nested template i.e. not grouped
	 						else $rowsInThisComp .= $this->aggregateOrSingle($value2, $currCompo, ['node'=>$pairIndex]);
	 					}
	 				}
		 			
		 			// completed all subunits in the given block
		 			if ($key == count($nestedComponent[0])-1 ) {

						// get position of the last foreach_*_end if context, in the absence of nested group
		 				preg_match('/'. preg_quote($fullMatch, '/'). '(.*)$/s', $context, $ftNoNestedGroups);

						preg_match('/(?:{{__foreach-'. strval($dimension+1) .'-end__}}(.*?))+?{{__foreach-'. $dimension .'-end__}}/s', $context, $ftWithNestedGroups);
						
						if (empty($ftNoNestedGroups)) {

							if (!empty($ftWithNestedGroups)){

				 				$rowFooter = $ftWithNestedGroups[1] .substr($context, strpos($context, '{{__foreach-'. $dimension .'-end__}}')+strlen('{{__foreach-'. $dimension .'-end__}}'));
				 			}

				 			else $rowFooter = '';
				 		}

			 			else {

			 				$tRFooter = $this->matchRecursive($value, $ftNoNestedGroups[1]);// will return an empty string if no placeholders were found

			 				$rowFooter = $tRFooter ?? $ftNoNestedGroups[1];
			 			}
			 		} // close handle last subunit

		 			if (!empty($rowsInThisComp)) $carry = $rowHeader . $rowsInThisComp . $rowFooter;
		 		}

			 	// try parsing whatever fields are in the given context/header
			 	else return $this->matchRecursive($value, $context);
			}

			elseif (isset($dataSet[$key])) {

				// since this replace cb is already in an outer loop, match progressively instead of in one gulp
				$currIter = preg_replace_callback("/\{\{($key)\}\}/i", function($match) use ($dataSet, $key) { return $dataSet[$key]; }, $tempContext );
				
				$tempContext =& $currIter; // reference assignment can only be between variables, not values

				$carry = $tempContext;
			}
			
		}

		return $carry;
	}


	/**
	* @description: Spins a dataset over a given context if the given key is found in it and it's a multi-dimensional array. Otherwise, parses the context just once
	*
	* @param {$opts}:Array with following keys
		node - str or int key to read from in this current dataSet (compulsory)
		header - precede aggregate data with this str
		footer - succeed aggregate data with this str
	*/
	private function aggregateOrSingle (array $ds, string $ctx, array $opts) {

		$unitContent = ''; $node = $opts['node']; $header = @$opts['header'];

		$footer = @$opts['footer'];

		$isSub = isset($ds[$node]) && count(
			array_filter($ds[$node], function($value3) {

				return is_array($value3);
			})
		) === count($ds[$node]);

		if ($isSub) {

			foreach ($ds[$node] as $key2 => $value2) {

				$unitContent .= $this->matchRecursive($value2, $header .$ctx. $footer);
			}
		}

		else $unitContent = $this->matchRecursive($ds, $ctx);

		return $unitContent;
	}


	/**
	 * @description: Iterate as deep as possible until we find the node bearing this key
	 *
	 * @return containing array where given key was found or null
	 **/
	private function dSetSearch ($toSearch, $val, $found=null) {

		if (!is_null($found) && array_key_exists($toSearch, $found)) return $found;

		foreach ($val as $key1 => $value1) {

			if ($key1 == $toSearch) {

				return $this->dSetSearch($toSearch, $value1, $value1);
			}

			if (is_array($value1)) {

				$tempRes = $this->dSetSearch($toSearch, $value1);

				if (!is_null($tempRes)) {

				 	return $this->dSetSearch($toSearch, $value1, $val);
				}
	 		}
	 	}
	}


	public function parseAll () {

		[$staticVars, $repeatedComponents ] = $this

		->findSingleAndGrouped(
			
			$this->formatToUsersView($this->viewData)
		);

		$enoughData = count($repeatedComponents) >= $this->fields()['blockCount'];

		$this->staticVars = $staticVars;

		//try/catch doesn't work for Fatal Errors so
		register_shutdown_function([$this, 'shutdown']);

		// if we're expecting dynamic variables, preprocess them before parsing
		if (!empty($repeatedComponents) && $enoughData) {

			foreach ($repeatedComponents as $key => $components) { // components here is 2d arr of each row

				$accumulate = '';

				preg_match($this->forEachRegex, $this->file, $currentForEach);

				$toMatch = @$currentForEach[1];

				// store the parsed string for the file, then replace the placeholder in the current component with that variable
				if (preg_match($this->fileComponent, $toMatch, $isFile)) {

					$ctrl = $this->appContainer->getClass(GetController::class);

					$file = file_get_contents($this->appContainer->viewPath . $ctrl->nameCleanUp($isFile[1]) . '.tmpl');

					$tempAccum = ''; // each file instance


					preg_match_all($this->forEachRegex, $file, $allRepeated );


					foreach ($components as $key1 => $batchValues) {

						for ($i=0; $i < count($allRepeated[1]); $i++) {
						
							$currForEach = $allRepeated[1][$i]; // in the template text

							$res = $this->matchRecursive($batchValues, $currForEach);
						
							// replace current foreach with its contextual value
							$tempAccum .= preg_replace('/({{__foreach-1-start__}}([\s\S]+?){{__foreach-1-end__}})/', $res, $file, 1);
						}

						// handle single placeholders outside foreachs
						$tempAccum = $this->matchRecursive($batchValues, $tempAccum);
					}

					$accumulate .= $tempAccum;

					$this->file = preg_replace('/({{__foreach-1-start__}}([\s\S]+?){{__foreach-1-end__}})/', $accumulate, $this->file, 1);
				}


				else {
					
					ob_start();			

					foreach ($components as $key => $group) {

						$accumulate .= $this->matchRecursive($group, $toMatch);
					} 

					// if we get here, no error occured
					$anyMessage = ob_get_contents();

					ob_end_clean();

					echo $anyMessage;

					// replace the next foreach block only
					$this->file = preg_replace('/({{__foreach-1-start__}}([\s\S]+?){{__foreach-1-end__}})/', $accumulate, $this->file, 1);
				}
			}


			return preg_replace_callback($this->regex, function ($match) use ($staticVars) {
				
				return @$staticVars[$match[1]];
			}, $this->file);
		}
		
		else {

			// gracefully fail if insufficient data for the template is supplied
			if (!$enoughData) {

				$msg = 'This view requires '. $this->fields()['blockCount'] . ' data blocks. Found only '. count($repeatedComponents);

				$this->file = self::showMessage($msg) . preg_replace($this->forEachRegex, '', $this->file);
			}

			return preg_replace_callback($this->regex, function ($match) use ($staticVars) {
				
				return @$staticVars[$match[1]];
			}, $this->file);
		}
	}

	static function showMessage (string $msg) { return '<p style="color: #f00; font-size: 150%; margin:5%">'. $msg . '.</p>';}

	/**
	* @description set key 'navIndicator' in the dataset to correspond to resource name
	*/
	private function setNavActive ( ) {

		if ($this->appContainer->getActiveRoute()->appendHeader) {

			$ctrl = $this->appContainer->getClass(GetController::class); // if this method should still be here, bind parent GetController to the child so fetching it here will return the proper ContentOptions

			$config = $ctrl->getContentOptions();

			$sVars = $this->staticVars; // expected to contain menu items on this page

			$key = 'navIndicator';
			
			if (isset($config[$key]) && is_callable($config[$key]))

				$this->staticVars[$key] = $config[$key]($sVars);

			else $this->staticVars[$key] = 'active_'. $ctrl->nameDirty(@$sVars['name'], 'dash-case'); // resource name
		}
	}

	private function assignFile ( ):void {

		$viewPath = $this->appContainer->viewPath;

		$route = $this->appContainer->getActiveRoute();

		$this->file = file_get_contents($viewPath . $route->viewName . '.tmpl');

		if ($route->appendHeader) $this->file = file_get_contents($viewPath . 'header.tmpl') . $this->file . file_get_contents($viewPath . 'footer.tmpl');
	}

	private function setRegex( ) {		

		$this->regex = '/\{\{(\w+)\}\}/';

		$this->forEachRegex = '/{{__foreach-1-start__}}([\s\S]+?){{__foreach-1-end__}}/';

		$this->fileComponent = '/{{__file-start__}}(?:\s+)?\{\{([\w]+)\}\}(?:\s+)?{{__file-end__}}/';
	}

	public function shutdown() {

	    $error = error_get_last(); 
	    
	    if ($error['type'] === E_ERROR) {

	    	$msg = $error['message']; preg_match('/TypeError: Argument (\d)/', $msg, $prob);

	    	
	    	$correct = ['array', 'string']; $args = ['dataSet', 'context']; $ind = +$prob[1]-1;

	    	
	    	preg_match('/((->matchRecursive)|(\{closure\}))\((.+?)\)/', $msg, $argVals);

	    	
	    	$params = explode(', ', $argVals[4]);

	    	
	    	$oppos = array_filter($params, function ($l) use ($ind) { return $l != $ind;}, ARRAY_FILTER_USE_KEY);

	    	$fn = $argVals[1] == '->matchRecursive' ? 'matchRecursive': 'aggregate data set';

			$z = ob_get_contents(); // notices or logs
	    	
	    	ob_end_clean();
		var_dump($msg, $params, $oppos);
	    	$errOutput = 'Invalid '. $args[$ind]. ' supplied to '. $fn .'. Expected '. $correct[$ind] . " but found \n\n". $params[$ind] . '. Does not match given ' . $args[key($oppos)] . ': '. $oppos[key($oppos)];

	    	echo TemplateEngine::showMessage($errOutput), $z/*, $msg*/;
	    }
	}

	private function findSingleAndGrouped ($data):array {
		
		$repeatedComponents = array_filter($data, "is_array");

		return [@array_diff($data, $repeatedComponents), $repeatedComponents];
	}

	/** 
	* @description: takes care of formatting multi-nested dataSet for templating
	*/
	protected function formatToUsersView ( array $dataSet ):array {

		$newVals = [];

		foreach ($dataSet as $formatName => $block) {
			
			if (is_array($block)) {

				foreach ($block as &$row) {

	 				$transforms = $this->sourceClass->semanticTransforms();

		 			if (array_key_exists($formatName, $transforms))

		 				$row = $transforms[$formatName]($row);
				}

				$newVals[] = $block;
			}

			else $newVals[$formatName] = $block;
		}

		return $newVals;
	}
}

?>