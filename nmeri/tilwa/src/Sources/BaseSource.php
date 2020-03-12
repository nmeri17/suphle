<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	/**
	*	@Note: In cases where a non-existent invalid resource or malformed parameters are requested, throwing `new Error`, will trigger a 404 error page
	 */
	 class BaseSource {

		// public $dataBlocks;

		public $validator;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}

		/** 
		* @description: takes care of formatting multi-nested dataSet for templating
		*/
		final public function formatForEngine ( array $dataSet ):array {
	 		
	 		return [ array_map( function ($block) {

				if (is_array($block)) array_walk( $block, function (&$row, $key) {
	 			var_dump($row);
	 				$transforms = $this->semanticTransforms();

		 			if (array_key_exists($key, $transforms))

		 				$row = $transforms[$key]($row);
		 		});

				return $block;
			}, $dataSet) ];
		}

		// bridge the gap between front end semantics & row data
	 	protected function semanticTransforms ():array {

	 		return [];
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