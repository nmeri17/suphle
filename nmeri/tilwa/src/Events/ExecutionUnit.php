<?php
	namespace Tilwa\Events;

	use Tilwa\Errors\{UnauthorizedServiceAccess, InvalidRepositoryMethod};

	class ExecutionUnit {

		private $eventName;

		private $handlingMethod;
		
		function __construct(string $eventName, string $handlingMethod) {

			$this->eventName = $eventName;

			$this->handlingMethod = $handlingMethod;
		}

		public function canExecute(string $eventName):bool {
			
			return $eventName ==  $this->eventName;
		}

		public function fire(object $hydratedHandler, $payload) {
			
			return call_user_func_array(
				[$hydratedHandler, $this->handlingMethod ],
				[$payload]
			);
		}
	}
?>