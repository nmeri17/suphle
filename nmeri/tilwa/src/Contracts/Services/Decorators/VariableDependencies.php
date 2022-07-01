<?php
	namespace Tilwa\Contracts\Services\Decorators;

	interface VariableDependencies extends ModifiesArguments {

		/**
		 * @return [publicPropertyToSet => className]
		*/
		public function dependencyNames ():array;
	}
?>