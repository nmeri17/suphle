<?php
	namespace Tilwa\Contracts\Modules;

	use Tilwa\Hydration\Container;

	interface DescriptorInterface {

		/**
		 * Interface which will be consumers' API on this module
		*/
		public function exportsImplements ():string;

		public function getContainer ():Container;
	}
?>