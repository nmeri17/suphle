<?php

	namespace Tilwa\Controllers;

	// exists for the sole purpose of enforcing update queries go through one channel [emit]. By knowing such queries beforehand, we can easily control what happens to cached copies of its fetched objects
	class AlterCommands extends InterceptsQuery {

		public function modelOperations() {
			# set event handler on model blocking reads if the sub has no ancestor by our name
		}

		# don't even call the method if this is false
		public function canCommand():bool {
			return true;
		}
	}
?>