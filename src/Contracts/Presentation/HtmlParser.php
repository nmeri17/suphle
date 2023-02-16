<?php
	namespace Suphle\Contracts\Presentation;

	interface HtmlParser {

		public function parseRenderer (RendersMarkup $renderer):string;

		public function findInPath (string $markupPath):void;
	}
?>