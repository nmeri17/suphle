<?php
	namespace Tilwa\Testing\Generators;

	class TrapQueryParameters {
		// previous execution unit for alters commands. should be unified with query permission check

		public $reboundContext;

		// this should be registered as an event handler on the "before_call" event
		private function setQueryListener($hydratedHandler, $payload):void {

			$hydratedHandler->getOrm()->setTrap($this->capturePrepared($hydratedHandler::class));
		}

		// if it turns out this handler is attached/triggered on each call to the db (remember queries send theirs too), it will have to be refactored to use something on the orm indicating this listener is already attached
		private function capturePrepared() {
			
			return function($bindings) {

				$method = $this->handlingMethod;

				if (empty($bindings)) // $query->bindings

					throw new InvalidRepositoryMethod($method);

				// the fetch event should only run when lifecycle is turned on
				
				$this->reboundContext = compact("bindings", "method"); // "fetched", compact("bindings")
			};
		}

		// should go into the repo wrapper
		$scope->getEventManager()->emit($hydratedHandler::class, "refresh", $executionUnit->reboundContext); // scope was the event subscription instance
	}
?>