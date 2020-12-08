<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	use Tilwa\Http\Response\Templating\TemplateEngine;

	class Markup extends Route {

		public $viewName; // setting this to false SHOULD skips the trip to parse. If null, it SHOULD assign the name of your handler to it

		function __construct(string $viewName, bool $appendHeader = true) {

			$this->viewName = $viewName;
		}

		public function renderResponse() {
			
			return $this->publishHtml();
		}
	}
?>