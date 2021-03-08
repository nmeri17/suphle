<?php

	namespace Tilwa\Controllers;

	use Tilwa\Events\EventManager;

	class QueryEmitter {

		protected $eventManager;

		private $eventEmitter;

		public function __construct(EventManager $eventManager) {
			
			$this->eventManager = $eventManager;
		}

		public function emit(string $eventName, $payload):void {
			
			$this->eventManager->emit( $this->eventEmitter, $eventName, $payload);

			// emitting the "refresh" event here works for synchronous events. otherwise, this counter emission will have to wait until that guy completes
			// will need access to the AlterCommand that handled the event so we can pull the prepared arguments. eventManager has a getLocalHandler($emitter); method
		}

		public function setEmitter() {
			# triggered by loadService on the controller if it detects this as one of the injected services
		}
	}
?>