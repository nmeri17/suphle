<?php
	namespace Tilwa\Tests\Interactions;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Contracts\Services\Decorators\MultiUserModelEdit;

	interface ModuleOne {

		const DEFAULT_EVENT = "sample_event";

		const EMPTY_PAYLOAD_EVENT = "no_payload";

		const OUTSIDERS_REBOUND_EVENT = "outsiders_rebound";

		public function setBCounterValue (int $newCount):void;

		public function getBCounterValue ():int;

		public function noPayloadEvent ():void;

		public function payloadEvent (int $value):void;

		public function cascadeEntryEvent (int $value):void;

		public function sendConcatEvents (int $value):void;

		public function sendExtendedEvent (int $value):void;

		public function multiModuleCascadeEvent (bool $value);

		public function getResourceEditor ():MultiUserModelEdit;

		public function systemUpdateErrorEvent (int $payload):?OptionalDTO;
	}
?>