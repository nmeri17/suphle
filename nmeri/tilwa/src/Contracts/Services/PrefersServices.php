<?php
	namespace Tilwa\Contracts\Services;

	interface PrefersServices { // this hasn't been implemented yet

		public function getAllowed ():array;

		public function getDenied ():array;
	}
?>