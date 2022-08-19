<?php
	namespace Suphle\Tests\Mocks\Interactions;

	interface ModuleOne {

		const DEFAULT_EVENT = "sample_event",

		EMPTY_PAYLOAD_EVENT = "no_payload",

		OUTSIDERS_REBOUND_EVENT = "outsiders_rebound";

		public function setBCounterValue (int $newCount):void;

		public function getBCounterValue ():int;

		public function noPayloadEvent ():void;

		public function payloadEvent (int $value):void;

		public function cascadeEntryEvent (int $value):void;

		public function sendConcatEvents (int $value):void;

		public function sendExtendedEvent (int $value):void;

		public function multiModuleCascadeEvent (bool $value);

		public function systemUpdateErrorEvent (int $payload):?int;
	}
?>