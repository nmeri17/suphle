<?php
	namespace Suphle\Contracts\Presentation;

	interface HtmlParser {

		public function parseAll(...$arguments):string;
	}
?>