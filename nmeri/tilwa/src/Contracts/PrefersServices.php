<?php
	namespace Tilwa\Contracts;

	interface PrefersServices {

		public function getAllowed ():array;

		public function getDenied ():array;
	}
?>