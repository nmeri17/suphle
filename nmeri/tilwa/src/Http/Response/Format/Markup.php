<?php

	namespace Tilwa\Http\Response\Format;

	use Tilwa\Routing\Route;

	use Tilwa\Http\Response\Templating\TemplateEngine;

	class Markup extends Route {

		public function renderResponse() {
			
			return $this->publishHtml();
		}
	}
?>