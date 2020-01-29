<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	/**
	*	@description: Methods on this class are required to fetch live data from relevant sources and return them in a presentable format to be piped into their respective views. Except in the case of internally used methods, method names on this class correspond to the name of the view of the route that called it.
	*
	*	In cases where a non-existent invalid resource or malformed parameters are requested, throwing `new TypeError`, will trigger a 404 error page
	 */
	 class BaseSource {

		// public $dataBlocks;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}

		/** 
		* @description: takes care of formatting multi-nested dataSet for templating
		* @return: the transformed `dataSet`
		*/
		public function formatForEngine (array $dataSet ):array {
	 		
	 		$anyAdditionalKeys = array_map(function (&$arr) {

				return [$this->semantics($arr)];
			}, $dataSet);

	 		return [			

				'fullTable'=> [
					'allRows' => $anyAdditionalKeys
				],
			];
		}

		// bridge the gap between front end semantics & row data
	 	protected function semantics (array $data):array {

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