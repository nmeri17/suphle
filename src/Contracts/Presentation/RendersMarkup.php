<?php
	namespace Suphle\Contracts\Presentation;

	interface RendersMarkup {

		public function getMarkupName ():string;

		/**
		 * @param {markupPath}: should have trailing slash
		*/
		public function setFilePath (string $markupPath):self;
	}
?>