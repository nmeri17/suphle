<?php
	namespace Tilwa\Events;

	use Tilwa\Errors\{UnauthorizedServiceAccess, InvalidRepositoryMethod};

	use Tilwa\Controllers\AlterCommands;

	class ExecutionUnit {

		private $eventName;

		private $handlingMethod;

		public $reboundContext;
		
		function __construct(string $eventName, string $handlingMethod) {

			$this->eventName = $eventName;

			$this->handlingMethod = $handlingMethod;
		}

		public function canExecute(string $eventName):bool {
			
			return $eventName ==  $this->eventName;
		}

		public function fire(object $hydratedHandler, $payload) {
			
			if ($hydratedHandler instanceof AlterCommands && $hydratedHandler->reboundsEvents())

				$this->setQueryListener( $hydratedHandler, $payload);
			
			return $this->getOperationResult($hydratedHandler, $payload);
		}

		private function setQueryListener(object $hydratedHandler, $payload):void {

			if (!$hydratedHandler->canCommand($method, $payload))

				throw new UnauthorizedServiceAccess($hydratedHandler::class);

			$hydratedHandler->ormListener($this->capturePrepared($hydratedHandler::class));
		}

		private function getOperationResult( $hydratedHandler, $payload) {
			
			return call_user_func_array(
				[$hydratedHandler, $this->handlingMethod ],
				[$payload]
			);
		}

		// if it turns out this handler is attached/triggered on each call to the db (remember queries send theirs too), it will have to be refactored to use something on the orm indicating this listener is already attached
		private function capturePrepared() {
			
			return function($bindings) {

				$method = $this->handlingMethod;

				if (empty($bindings))

					throw new InvalidRepositoryMethod($method);
				
				$this->reboundContext = compact("bindings", "method");
			};
		}
	}
?>