<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	/**
	*	@Note: In cases where a non-existent invalid resource or malformed parameters are requested, throwing `new TypeError`, will trigger a 404 error page
	 */
	 class BaseSource {

		// public $dataBlocks;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}

		/** 
		* @description: takes care of formatting multi-nested dataSet for templating
		*/
		public function formatForEngine (array $dataSet ):array {
	 		
	 		$transformed = array_map(function (&$arr) {

	 			return /*[*/$this->semantics($arr)/*]*/;
			}, $dataSet);

	 		return [			

				/*'fullTable'=> [
					'allRows' =>*/ $transformed
				/*],*/
			];
		}

		// bridge the gap between front end semantics & row data
	 	protected function semantics ($data):array {

	 		return $data;
	 	}

		// restricted area
	 	public function metaDetails ( $name) {

	 		$breadcrumbs = [];

		 	$breadcrumbs['og_url'] = $postUrl = urlencode('app url'. $name);

		 	$desc = '';
		 		
		 	// share buttons
	 		$breadcrumbs['share_twitter'] = 'https://twitter.com/intent/tweet/?text=' . urlencode($desc. ' #MyHashTag'). '&url=' . $postUrl . '&via=';

	 		$breadcrumbs['share_fb'] = 'https://www.facebook.com/sharer/sharer.php?u=' . $postUrl;

	 		$breadcrumbs['description'] = $desc;

	 		return $breadcrumbs;
	 	}
	}

?>