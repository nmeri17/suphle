<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface VariableDependencies {

		/**
		 * @return [method names]
		*/
		public function dependencyMethods ():array;
	}
?>