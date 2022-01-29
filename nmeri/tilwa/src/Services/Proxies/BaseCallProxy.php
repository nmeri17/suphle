<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use ReflectionMethod;

	abstract class BaseCallProxy {

		protected $activeService, $exceptionDetector;

		public function __construct (DetectedExceptionManager $exceptionDetector) {

			$this->exceptionDetector = $exceptionDetector;
		}

		abstract public function artificial__call (string $method, array $arguments);

		protected function yield (string $method, array $arguments = []) {

			return call_user_func_array([$this->activeService, $method], $arguments);
		}

		public function setConcrete ($instance):void {

			$this->activeService = $instance;
		}

		private function failureReturnValue(string $method) {

			$default = null;
			
			$reflectedMethod = new ReflectionMethod($this->activeService, $method);

			$type = $reflectedMethod->getPrototype()->getReturnType();

			settype($default, $type);

			return $default;
		}

		protected function attemptDiffuse (Throwable $exception, string $method) {

			$this->exceptionDetector->detonateOrDiffuse($exception, $this->activeService);

			$callerResponse = $this->activeService->failureState($method);

			return $callerResponse ?? $this->failureReturnValue($method);
		}
	}
?>