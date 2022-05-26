<?php
	namespace Tilwa\Services\Proxies;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Hydration\Structures\{ObjectDetails, BuiltInType};

	use ReflectionType;

	abstract class BaseCallProxy {

		protected $activeService, $exceptionDetector, $objectMeta,

		$container, $typeSetter;

		public function __construct (DetectedExceptionManager $exceptionDetector, ObjectDetails $objectMeta, BuiltInType $typeSetter) {

			$this->exceptionDetector = $exceptionDetector;

			$this->objectMeta = $objectMeta;

			$this->typeSetter = $typeSetter;
		}

		abstract public function artificial__call (string $method, array $arguments);

		protected function yield (string $method, array $arguments = []) {

			return call_user_func_array([$this->activeService, $method], $arguments);
		}

		public function setConcrete (object $instance):void {

			$this->activeService = $instance;
		}

		protected function attemptDiffuse (Throwable $exception, string $method):OptionalDTO {

			$this->exceptionDetector->detonateOrDiffuse($exception, $this->activeService);

			$callerResponse = $this->activeService->failureState($method);

			return $callerResponse ?? $this->buildFailureContent($method);
		}

		private function buildFailureContent (string $method):OptionalDTO {

			$returnType = $this->objectMeta->methodReturnType(

				get_class($this->activeService), $method
			);

			if ( (new ReflectionType($returnType))->isBuiltin())

				$typeDummy = $this->typeSetter->getDefaultValue($returnType);

			else $typeDummy = $this->container->getClass($returnType); // replace with null proxy

			return new OptionalDTO($typeDummy, false );
		}
	}
?>