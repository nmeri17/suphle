<?php
	namespace Tilwa\Tests\Interactions;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Events\LocalReceiver, Concretes\LocalSender}; // ideally, these entities should equally exist within this namespace, as well

	interface ModuleOne {

		public function getLocalSender ():LocalSender;

		public function getLocalReceiver ():LocalReceiver;

		public function setBCounterValue (int $newCount):void;

		public function getBCounterValue ():int;
	}
?>