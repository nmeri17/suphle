<?php
	namespace Suphle\Contracts\Auth;

	interface UserHydrator {

		public function findById (string $id):?UserContract;

		/**
		 * pull email/username/any field you are interested in from [PayloadStorage] and fetch that from ORM's user model
		*/
		public function findAtLogin ():?UserContract;

		public function setUserModel (UserContract $model):void;
	}
?>