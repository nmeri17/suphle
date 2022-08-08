<?php
	namespace Suphle\Contracts\Presentation;

	interface TransphpormRenderer extends RendersMarkup {

		public function getTemplatePath ():string;

		public function getRawResponse ():array;

		public function setFilePaths (string $markupPath, string $templatePath):self;
	}
?>