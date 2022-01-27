<?php
	namespace Tilwa\Controllers\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use ReflectionMethod;

	use Throwable;

	class UpdatelessCallProxy extends BaseCallProxy {

		private $exceptionDetector;

		public function __construct (DetectedExceptionManager $exceptionDetector) {

			$this->exceptionDetector = $exceptionDetector;
		}

		public function artificial__call (string $method, array $arguments) {

			$result = null;

			try {

				$result = $this->yield($method, $arguments);
			}
			catch (Throwable $exception) {

				$this->exceptionDetector->detonateOrDiffuse($exception, $this->activeService);

				$result = $this->failureReturnValue($method);
			}
			return $result;
		}

		private function failureReturnValue(string $method) {

			$default = null;
			
			$reflectedMethod = new ReflectionMethod($this->activeService, $method);

			$type = $reflectedMethod->getPrototype()->getReturnType();

			settype($default, $type);

			return $default;
		}
	}
?>