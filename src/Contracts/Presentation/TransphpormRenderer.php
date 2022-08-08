<?php
	namespace Suphle\Contracts\Presentation;

	interface TransphpormRenderer extends RendersMarkup {

		public function getTemplatePath ():string;

		public function getRawResponse ():iterable;

		public function setFilePaths (string $markupPath, string $templatePath):self;
	}
?>