<?php
	namespace Tilwa\Events;

	trait EmitProxy {

		protected function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(get_class($this), $eventName, $payload);
		}
	}
?>