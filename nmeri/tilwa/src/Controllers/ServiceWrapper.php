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

		protected function yield (string $method, array $arguments) {

			$service = $this->activeService;

			if ($service instanceof PermissibleService && !$service->canPerform($method, $arguments))

				throw new UnauthorizedServiceAccess($service::class);
			
			return $service->$method(...$arguments);
		}

		private function failureReturnValue(string $method) {

			$default = null;
			
			$reflectedMethod = new ReflectionMethod($this->activeService, $method);

			$type = $reflectedMethod->getPrototype()->getReturnType();

			settype($default, $type);

			return $default;
		}

		public function setActiveService(object $service):void {
			
			$this->activeService = $service;
		}
	}
?>