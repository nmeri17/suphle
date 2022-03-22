<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface SelectiveDependencies {

		public function getPermitted ():array;

		public function getRejected ():array;
	}
?>