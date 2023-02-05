<?php
	namespace Suphle\Events;

	use Suphle\Contracts\Events;

	/**
	 * Requires an `eventManager` property to be set on using classes
	*/
	trait EmitProxy {

		protected readonly Events $eventManager;

		protected function emitHelper (string $eventName, $payload = null):void {

			$this->eventManager->emit(static::class, $eventName, $payload);
		}
	}
?>