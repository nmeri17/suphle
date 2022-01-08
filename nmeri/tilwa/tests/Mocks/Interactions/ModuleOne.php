<?php
	namespace Tilwa\Tests\Interactions;

	interface ModuleOne {

		public function setBCounterValue (int $newCount):void;

		public function getBCounterValue ():int;

		public function noPayloadEvent ():string;

		public function emittedEventName ():string;

		public function payloadEvent (int $value):void;

		public function getLocalReceivedPayload ():?int;
	}
?>