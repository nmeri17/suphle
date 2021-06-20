<?php

	namespace Tilwa\Contracts;

	// Each method is required to throw UnauthorizedServiceAccess when user is unauthorized
	interface ModelAuthorities {
		
		public function retrieved ($entity):bool;

		public function updating ($entity):bool;

		public function creating ($entity):bool;

		public function deleting ($entity):bool;
	}
?>