<?php
	namespace Tilwa\Tests\Mocks\Interactions;

	interface ModuleThree {

		public function getLocalValue ():int;

		public function changeExternalValueProxy (int $newCount):void;

		public function getExternalReceivedPayload ():?int;
	}
?>