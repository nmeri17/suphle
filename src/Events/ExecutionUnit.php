<?php
	namespace Suphle\Events;

	class ExecutionUnit {

		function __construct(private readonly string $eventName, private readonly string $handlingMethod)
  {
  }

		public function matchesEvent(string $eventName):bool {
			
			return $eventName ==  $this->eventName;
		}

		public function fire($hydratedHandler, $payload) {
			
			return call_user_func_array(
				[$hydratedHandler, $this->handlingMethod ],
				[$payload]
			);
		}
	}
?>