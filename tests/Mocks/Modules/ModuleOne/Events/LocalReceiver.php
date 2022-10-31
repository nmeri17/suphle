<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Events;

	use Suphle\Events\{EmitProxy, EventManager};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleApi;

	class LocalReceiver {

		use EmitProxy;

		final const CASCADE_REBOUND_EVENT = "rebounding";

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

			$this->emitHelper( self::CASCADE_REBOUND_EVENT, $payload);
		}

		public function unionHandler ($payload = null):void {

			$this->payload = $payload;
		}

		public function reboundExternalEvent ($payload):void {

			$this->emitHelper( ModuleApi::OUTSIDERS_REBOUND_EVENT, $payload);
		}
	}
?>