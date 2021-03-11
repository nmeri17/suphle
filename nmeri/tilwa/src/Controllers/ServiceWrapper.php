<?php

	namespace Tilwa\Controllers;

	use Tilwa\Errors\UnauthorizedServiceAccess;

	class ServiceWrapper {

		private $activeService;

		private $eventManager;

		private $lifeCycle;

		public function __construct (EventManager $eventManager, bool $lifeCycle) {

			$this->eventManager = $eventManager;

			$this->lifeCycle = $lifeCycle;
		}

		public function __call($method, ...$arguments) {

			$emitter = $this->activeService::class;

			if ($this->lifeCycle)

				$this->eventManager->emit($emitter, "before_call", compact("method", "arguments"));

			$result = null;

			try {
				$result = $this->yield($method, $arguments);

				if ($this->lifeCycle)

					$this->eventManager->emit($emitter, "after_call", compact("method", "result"));
			}
			catch (ErrorException $error) {

				$this->eventManager->emit($emitter, "error", compact("method", "arguments", "error"));

				$result = $this->failureReturnValue($method);
			}
			return $result;
		}

		private function yield (string $method, array $arguments) {
			return $this->activeService->$method(...$arguments);
		}

		private function failureReturnValue(string $method) {
			
			$reflectedMethod = new ReflectionMethod($this->activeService, $method);

			return $reflectedMethod->getPrototype()->getReturnType() ;
		}

		public function setActiveService(object $service):void {
			
			$this->activeService = $service;
		}
	}
?>