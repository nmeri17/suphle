<?php
	namespace Suphle\Events;

	/**
	 * Requires an `eventManager` property to be set on using classes
	*/
	trait EmitProxy {

		private $eventManager;

		protected function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(static::class, $eventName, $payload);
		}
	}
?>