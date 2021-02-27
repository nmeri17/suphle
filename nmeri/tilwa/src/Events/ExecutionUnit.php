<?php
	namespace Tilwa\Events;

	class ExecutionUnit {

		public $eventName;

		public $handlingMethod;
		
		function __construct(string $eventName, string $handlingMethod) {

			$this->eventName = $eventName;

			$this->handlingMethod = $handlingMethod;
		}
	}
?>