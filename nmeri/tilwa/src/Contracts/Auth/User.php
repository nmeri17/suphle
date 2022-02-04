<?php
	namespace Tilwa\Contracts\Auth;

	interface UserContract {

		public function getId ();

		public function setId ($id):void;

		public function getPassword ();

		public function find ():UserContract;
	}
?>