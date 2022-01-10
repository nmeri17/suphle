<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Events;

	use Tilwa\Events\EventManager;

	class LocalReceiver {

		const CASCADE_REBOUND_EVENT = "rebounding";

		private $eventManager;

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

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