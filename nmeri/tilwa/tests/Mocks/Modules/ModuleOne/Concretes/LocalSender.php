<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	class LocalSender {

		const DEFAULT_EVENT = "sample_event";

		const EMPTY_PAYLOAD_EVENT = "no_payload";

		private $eventManager, $lastEmittedEvent;

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function sendLocalEvent ($payload):void {

			$this->lastEmittedEvent = self::DEFAULT_EVENT;

			$this->eventManager->emit(get_class(), $this->lastEmittedEvent, $payload);
		}

		public function sendLocalEventNoPayload ():void {

			$this->lastEmittedEvent = self::EMPTY_PAYLOAD_EVENT;

			$this->eventManager->emit(get_class(), $this->lastEmittedEvent);
		}

		public function getEventName ():string {

			return $this->lastEmittedEvent;
		}
	}
?>