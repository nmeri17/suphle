<?php

	namespace Tilwa\Controllers;

	use Tilwa\Contracts\Config\Services;

	use Tilwa\Controllers\Structures\ServiceEventPayload;

	use Throwable;

	class ServiceWrapper {

		private $activeService, $eventManager, $config;

		public function __construct (EventManager $eventManager, Services $config) {

			$this->eventManager = $eventManager;

			$this->config = $config;
		}

		public function __call($method, $arguments) {

			$emitter = get_class($this->activeService);

			$result = null;

			try {

				$result = $this->yield($method, $arguments);
			}
			catch (Throwable $error) {

				$payload = new ServiceEventPayload($arguments, $method);

				$payload->setErrors($error);

				$this->eventManager->emit($emitter, "error", $payload); // NOTE: emitter is not the guy for this. contact the error notifier

				$result = $this->failureReturnValue($method);
			}
			return $result;
		}

		protected function yield (string $method, array $arguments) {

			return call_user_func_array([$this->activeService, $method], $arguments);
		}

		private function failureReturnValue(string $method) {

			$default = null;
			
			$reflectedMethod = new ReflectionMethod($this->activeService, $method);

			$type = $reflectedMethod->getPrototype()->getReturnType();

			settype($default, $type);

			return $default;
		}

		public function setActiveService($service):void {
			
			$this->activeService = $service;
		}
	}
?>