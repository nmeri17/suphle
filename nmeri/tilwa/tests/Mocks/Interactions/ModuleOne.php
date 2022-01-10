<?php
	namespace Tilwa\Tests\Interactions;

	interface ModuleOne {

		const DEFAULT_EVENT = "sample_event";

		const EMPTY_PAYLOAD_EVENT = "no_payload";

		public function setBCounterValue (int $newCount):void;

		public function getBCounterValue ():int;

		public function noPayloadEvent ():void;

		public function payloadEvent (int $value):void;

		public function cascadeEntryEvent (int $value):void;

		public function sendConcatEvents (int $value):void;
	}
?>