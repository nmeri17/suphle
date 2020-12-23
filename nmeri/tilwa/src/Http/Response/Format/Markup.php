<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	class Markup extends Route {

		public $viewName;

		public $appendHeader;

		private $viewModels;

		function __construct( string $viewName, bool $appendHeader = true, array $viewModels = []) {

			$this->viewName = $viewName;

			$this->appendHeader = $appendHeader;

			$this->viewModels = $viewModels;
		}

		public function renderResponse(HtmlParser $htmlAdapter) {
			
			// you want to call this->runViewModels somewhere here
			return $this->publishHtml($htmlAdapter);
		}

		public function runViewModels () {
			
			# it is expected that html formatting will be done here instead of the view template. we want viewModels to contain an array of Class@handler with which we can use to further parse template for that specific request
		}
	}
?>