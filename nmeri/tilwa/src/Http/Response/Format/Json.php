<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Json extends Route {

		function __construct() {

			parent::__construct();
		}

		public function renderResponse() {
			
			return $this->publishJson();
		}
	}
?>