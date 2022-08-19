<?php
	namespace Suphle\Events;

	trait EmitProxy {

		protected function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(get_called_class(), $eventName, $payload);
		}
	}
?>