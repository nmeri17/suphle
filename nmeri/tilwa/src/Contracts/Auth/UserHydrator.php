<?php

	namespace Tilwa\Contracts\Auth;

	interface UserHydrator {

		public function findById(string $id);

		/**
		 * pull email/username/any field you are interested in from [PayloadStorage] and fetch that from ORM's user model
		*/
		public function findAtLogin():User;
	}
?>