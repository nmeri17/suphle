<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes;

	class LocalSender {

		private $eventManager, $defaultEvent = "sample_event";

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function sendLocalEvent ($payload):void {

			$this->eventManager->emit(get_class(), $this->defaultEvent, $payload);
		}

		public function sendLocalEventNoPayload ():void {

			$this->eventManager->emit(get_class(), "no_payload");
		}

		public function getEventName ():string {

			return $this->defaultEvent;
		}
	}
?>