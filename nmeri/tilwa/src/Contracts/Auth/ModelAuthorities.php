<?php
	namespace Tilwa\Contracts\Auth;

	// Each method is required to throw UnauthorizedServiceAccess when user is unauthorized
	interface ModelAuthorities {
		
		public function retrieved ($entity):bool;

		public function updating ($entity):bool;

		/**
		 * Runs before insertion, so no entity. Work with user
		*/
		public function creating ():bool;

		public function deleting ($entity):bool;
	}
?>