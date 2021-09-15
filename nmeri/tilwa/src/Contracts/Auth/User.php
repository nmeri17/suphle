<?php
	namespace Tilwa\Contracts\Auth;

	interface User {

		public function getId ();

		public function getPassword ();
	}
?>