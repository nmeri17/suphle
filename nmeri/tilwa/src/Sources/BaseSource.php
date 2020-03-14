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

		// bridge the gap between front end semantics & row data
	 	public function semanticTransforms ():array {

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