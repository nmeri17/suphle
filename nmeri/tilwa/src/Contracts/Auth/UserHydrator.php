<?php

	namespace Tilwa\Contracts\Auth;

	interface UserHydrator {

		public function findById(string $id);

		public function findAtLogin(); // pull email/username/any field you are interested in from requestDetails and fetch that from ORM's user model
	}
?>