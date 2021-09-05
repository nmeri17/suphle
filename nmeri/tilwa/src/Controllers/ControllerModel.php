<?php

	namespace Tilwa\Contracts;

	/**
	 * Nothing can be done with this guy in the controller. Must be passed to a service
	*/
	interface ControllerModel { // change this to an abstract class so [setIdentifier] can be fleshed out

		// @return a query builder
		public function getBuilder(); // so this guy can use inputStorage however he wants

		public function setInputStorage(string $modelId):void; // the request is validated (with action method name o) before getting here
	}
?>