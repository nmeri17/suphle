<?php
	namespace Suphle\Contracts\Presentation;

	interface HtmlParser {

		public function parseAll (RendersMarkup $renderer):string;

		public function findInPath (string $markupPath):void;
	}
?>