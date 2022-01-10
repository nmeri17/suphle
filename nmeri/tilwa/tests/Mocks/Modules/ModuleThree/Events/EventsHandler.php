<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Events;

	class EventsHandler {

		private $payload;

		public function setExternalPayload (int $payload) {
			
			$this->payload = $payload;
		}

		public function reactToExternalEmit (int $payload) {
			
			$this->payload = $payload;
		}
	}
?>