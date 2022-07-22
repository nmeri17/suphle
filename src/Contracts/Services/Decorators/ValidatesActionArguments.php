<?php
	namespace Suphle\Contracts\Services\Decorators;

	interface ValidatesActionArguments {

		public function permittedArguments ():array;
	}
?>