<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	class LocalReceiver extends PayloadReceptor {

		const CASCADE_REBOUND_EVENT = "rebounding";

		public function updatePayload ($payload):void {

			$this->payload = $payload;
		}

		public function doNothing ():void {

			//
		}

		public function reboundsNewEvent ($payload):void {

			$this->eventManager->emit(get_class(), self::CASCADE_REBOUND_EVENT, $payload);
		}
	}
?>