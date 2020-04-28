<?php

	namespace Tilwa\Sources;

	use Controllers\Bootstrap;

	/**
	*	throwing an Error will trigger a 404 error page
	 */
	 class BaseSource {

		// public $dataBlocks;

		public $validator;

		public $app;

		function __construct (Bootstrap $app ) {

			$this->app = $app;
		}

		// bridge the gap between front end semantics & row data
	 	public function semanticTransforms ():array {

	 		return [];
	 	}
	}

?>