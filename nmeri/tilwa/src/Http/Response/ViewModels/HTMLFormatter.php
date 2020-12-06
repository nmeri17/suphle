<?php

	namespace Tilwa\Response\ViewModels;

	use Tilwa\Helpers\Strings;

	/**
	* @description: will intercept data from controller and trigger all methods if response type is html
	*/
	class HTMLFormatter {

		private $request;

		function __construct(Request $request) {
			
			$this->request = $request;
		}

		/**
		*
		* @param {staticVars} expected to contain menu items on this response
		*/
		public function setNavIndicator ( array $staticVars):array {

			// highlight active menu inside a map function
			// alter key to 'active_'. Strings::nameDirty($this->request->path, 'dash-case');
			return $staticVars;
		}
	}
?>