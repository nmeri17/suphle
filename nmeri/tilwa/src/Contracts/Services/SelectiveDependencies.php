<?php
	namespace Tilwa\Contracts\Services;

	interface SelectiveDependencies {

		public function getPermitted ():array;

		public function getRejected ():array;
	}
?>