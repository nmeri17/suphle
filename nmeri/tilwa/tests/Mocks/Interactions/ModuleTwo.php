<?php
	namespace Tilwa\Tests\Interactions;

	interface ModuleTwo {

		public function getShallowValue ():int;

		public function setNestedModuleValue ():void;

		public function newExternalValue ():int;

		public function decoupledExternalReceivedPayload ():?int;
	}
?>