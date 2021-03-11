<?php

	namespace Tilwa\Controllers;

	// exists for the sole purpose of enforcing update queries go through one channel [emit]. By knowing such queries beforehand, we can easily control what happens to cached copies of its fetched objects
	class AlterCommands extends InterceptsQuery {

		public function modelOperations() {
			# set event handler on model blocking reads if the sub has no ancestor by our name
		}

		/**
		* @desc protects invocation of [method] when return value=false
		* @param {arguments} what the caller wants to pass to the method
		*/
		public function canCommand(string $method, array $arguments):bool {
			return true;
		}

		// causes all events handled by this class (each method call) to equally trigger ripple events ("refresh")
		public function reboundsEvents():bool {
			
			return true;
		}
	}
?>