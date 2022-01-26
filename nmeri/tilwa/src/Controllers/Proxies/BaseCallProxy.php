<?php
	namespace Tilwa\Controllers\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use Throwable;

	abstract class BaseCallProxy {

		private $exceptionDetector;

		protected $activeService;

		public function __construct (DetectedExceptionManager $exceptionDetector) {

			$this->exceptionDetector = $exceptionDetector;
		}

		abstract protected function yield (string $method, array $arguments);

		public function setConcrete ($instance):void {

			$this->activeService = $instance;
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