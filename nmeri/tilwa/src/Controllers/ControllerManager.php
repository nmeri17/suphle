<?php

	namespace Tilwa\Controllers;

	class ControllerManager {

		private $controller;

		private $eventManager;

		function __construct(Executable $controller) {
			
			$this->controller = $controller;
		}

		public function validate () {
			// validateServices (requires module dependencies), then registerFactories, hasIsolatedConstructor, setContainer
		}

		public function setActionArgument() {
			# code...
		}
	}
?>