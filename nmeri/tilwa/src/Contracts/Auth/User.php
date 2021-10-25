<?php
	namespace Tilwa\Contracts\Auth;

	interface User {

		public function getId ();

		public function getPassword ();

		public function find ():User;
	}
?>