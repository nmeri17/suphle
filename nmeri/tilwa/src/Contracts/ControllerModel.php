<?php

	namespace Tilwa\Contracts;

	interface ControllerModel {

		// @return a query builder
		public function getBuilder();

		public function setIdentifier(string $modelId):void;
	}
?>