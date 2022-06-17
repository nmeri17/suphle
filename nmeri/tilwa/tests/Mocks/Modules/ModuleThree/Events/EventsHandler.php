<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Events;

	use Tilwa\Events\{EmitProxy, EventManager};

	class EventsHandler {

		use EmitProxy;

		const EXTERNAL_LOCAL_REBOUND = "local_external_local";

		private $payload, $eventManager;

		public function __construct (EventManager $eventManager) {

			$this->eventManager = $eventManager;
		}

		public function setExternalPayload (int $payload) {
			
			$this->payload = $payload;
		}

		public function handleImpossibleEmit (int $payload) {
			
			$this->payload = $payload;
		}

		public function handleExternalRebound (bool $reboundInExternal) {

			if ($reboundInExternal)

				$this->emitHelper(self::EXTERNAL_LOCAL_REBOUND);
		}
	}
?>