<?php
	namespace Suphle\Tests\Mocks\Interactions;

	interface ModuleTwo {

		public function getShallowValue ():int;

		public function setNestedModuleValue (int $newCount):void;
	}
?>