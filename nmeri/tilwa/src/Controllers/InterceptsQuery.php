<?php

	namespace Tilwa\Controllers;

	use Tilwa\Events\EventManager;

	// sub-classed by db-level services
	abstract class InterceptsQuery {

		protected $permissions;

		abstract public function activeModel();

		// @desc determine whether [fetchModel] is authorized to be viewed, or whatever other available permission
		public function shouldFetch($fetchModel):bool {
			
			return true;
		}

		public function setPermissions() {
			# wire in the permissions object
		}

		# set event handler on model blocking writes if the sub has no ancestor by our name
		public function modelOperations() {
			
			$this->activeModel->attachEvent(writes) /* underground, we have ::created(function ($user) {
            //
        });*/
		}
	}
?>