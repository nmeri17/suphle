<?php

	namespace Tilwa\Controllers;

	use Tilwa\Errors\UnauthorizedServiceAccess;

	use Tilwa\Contracts\Config\Services;

	use Tilwa\Controllers\Structures\ServiceEventPayload;

	class ServiceWrapper {

		private $activeService, $eventManager, $config;

		public function __construct (EventManager $eventManager, Services $config) {

			$this->eventManager = $eventManager;

			$this->config = $config;
		}

		public function __call($method, $arguments) {

			$emitter = $this->activeService::class;

			if ($this->config->lifecycle())

				$this->eventManager->emit($emitter, "before_call", new ServiceEventPayload($arguments, $method));

			$result = null;

			try {
				$result = $this->yield($method, $arguments);

				if ($this->config->lifecycle())

					$this->eventManager->emit($emitter, "after_call", new ServiceEventPayload($result, $method));
			}
			catch (ErrorException $error) {

				$payload = new ServiceEventPayload($arguments, $method);

				$payload->setErrors($error);

				$this->eventManager->emit($emitter, "error", $payload);

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