<?php
	namespace Suphle\Contracts\Auth;

	interface UserContract {

		public function getId ();

		public function setId ($id):void;

		public function getPassword ();

		/**
		 * @return Entity. Maybe self
		*/
		public function findByPrimaryKey($key, $columns = ['*']);
	}
?>