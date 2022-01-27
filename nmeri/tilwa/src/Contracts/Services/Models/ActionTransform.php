<?php
	namespace Tilwa\Contracts\Services\Models;

	/**
	 * Use for requests where we need to convert payload to something that doesn't belong on our database i.e. callback from another service
	*/
	interface ActionTransform {

		public function getDomainObject ();
	}
?>