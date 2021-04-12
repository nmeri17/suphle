<?php

	namespace Tilwa\Contracts;

	interface PermissibleService {

		/**
		* @description protects invocation of [action] when this method returns false
		* @param {parameters} what the caller wants to pass to [action]
		*/
		public function canPerform(string $action, $parameters):bool;
	}
?>