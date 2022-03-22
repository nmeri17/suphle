<?php
	namespace Tilwa\Contracts\Presentation;

	interface HtmlParser {

		public function parseAll(...$arguments):string;
	}
?>