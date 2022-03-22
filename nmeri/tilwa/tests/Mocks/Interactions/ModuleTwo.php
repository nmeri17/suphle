<?php
	namespace Tilwa\Tests\Interactions;

	interface ModuleTwo {

		public function getShallowValue ():int;

		public function setNestedModuleValue (int $newCount):void;
	}
?>