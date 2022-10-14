<?php
	namespace Suphle\Events;

	/**
	 * Requires an `eventManager` property to be set on using classes
	*/
	trait EmitProxy {

		private $eventManager;

		protected function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(get_called_class(), $eventName, $payload);
		}
	}
?>