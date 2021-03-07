<?php

	namespace Tilwa\Controllers;

	use Tilwa\Events\EventManager;

	abstract class InterceptsQuery {

		protected $eventManager;

		private $eventEmitter;

		public function _setDependencies(EventManager $eventManager) {
			
			$this->eventManager = $eventManager;
		}

		// we still need the wrapper over the orm for interception
		public function emit(string $eventName, $payload) {
			
			$this->eventManager->emit( $this->eventEmitter, $eventName, $payload);
		}

		public function setEmitter() {
			# triggered by loadService on the controller if it detects this as one of the injected services
		}

		abstract public function activeModel();
	}
?>