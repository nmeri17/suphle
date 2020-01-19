<?php

	namespace Tilwa\Sources;

	use Tilwa\Controllers\GetController;

	/**
	*	@description: Methods on this class are required to fetch live data from relevant sources and return them in a presentable format to be piped into their respective views. Except in the case of internally used methods, method names on this class correspond to the name of the view (directory/parent content type) they're gathering data for.
	*
	*	In cases where a non-existent invalid resource or malformed parameters are requested, throwing `new TypeError`, will trigger a 404 error page
	 */
	 class Source {

		protected $cacheManager;

		public $dataBlocks;

		protected $container;

		function __construct ( $app ) {

			$this->container = $app;
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

	 		$vars = json_decode($this->container->getClass(GetController::class)->getContents($conn, $name), true);

	 		$breadcrumbs = [];

		 	$breadcrumbs['og_url'] = $postUrl = urlencode('app url'. $name);

		 	$desc = '';
		 		
		 	// share buttons
	 		$breadcrumbs['share_twitter'] = 'https://twitter.com/intent/tweet/?text=' . urlencode($desc. ' #BoardmanCarts'). '&url=' . $postUrl . '&via=';

	 		$breadcrumbs['share_fb'] = 'https://www.facebook.com/sharer/sharer.php?u=' . $postUrl;

	 		$breadcrumbs['description'] = $desc;

	 		return $breadcrumbs;
	 	}


	 	// gets the last ID in a given table and increments it by 1
		public function dbLastItem ( $table, $columnName='id') {

			$cache = $this->container->getClass(GetController::class)->cacheManager();

		    $cachedData = $cache->getItem(__FUNCTION__.'|'.$table);

		    $itemId = $cachedData->get();


		    if (is_null($itemId )) {

		 		$total = $this->container->getClass(GetController::class)->conn->prepare('SELECT `'. $columnName. "` FROM $table ORDER BY `". $columnName."` DESC LIMIT 1");

		 		$total->execute();

		 		$itemId = intval($total->fetch(PDO::FETCH_ASSOC)[$columnName])+1;

		 		$cachedData->set($itemId)->expiresAfter(600);
		 	}

		 	return $itemId;
		}
	}

?>