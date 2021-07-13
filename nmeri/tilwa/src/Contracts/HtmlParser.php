<?php
	namespace Tilwa\Contracts;

	interface HtmlParser {

		public function parseAll(...$arguments):string;
	}
?>