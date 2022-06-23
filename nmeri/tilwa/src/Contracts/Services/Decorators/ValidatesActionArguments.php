<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface ValidatesActionArguments {

		public function permittedArguments ():array;
	}
?>