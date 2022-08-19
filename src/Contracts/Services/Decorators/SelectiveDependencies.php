<?php
	namespace Suphle\Contracts\Services\Decorators;

	interface SelectiveDependencies {

		public function getPermitted ():array;

		public function getRejected ():array;
	}
?>