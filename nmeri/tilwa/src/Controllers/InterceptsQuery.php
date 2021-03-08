<?php

	namespace Tilwa\Controllers;

	use Tilwa\Events\EventManager;

	// sub-classed by db-level services
	abstract class InterceptsQuery {

		protected $permissions;

		abstract public function activeModel();

		// @desc determine whether [fetchModel] is authorized to be viewed, or whatever other available permission
		// will be refactored if there's a way to alter the query to include this check, before sending it off to the database
		public function shouldFetch($fetchModel):bool {
			
			return true;
		}

		// populated by either serviceWrapper or emitter before they call the service method i.e. two methods on this class they call that does the population
		public function trappedPrepared() {
			# pulled by service wrapper and emitter
		}

		public function setPermissions() {
			# wire in the permissions object
		}

		public function modelOperations() {
			# set event handler on model blocking writes if the sub has no ancestor by our name
		}
	}
?>