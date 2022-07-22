<?php
	namespace Suphle\Tests\Mocks\Interactions;

	interface ModuleThree {

		public function getLocalValue ():int;

		public function changeExternalValueProxy (int $newCount):void;
	}
?>