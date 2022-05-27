<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Hydration\Structures\{ObjectDetails, BuiltInType, DecoratorCallResult};

	use ReflectionType;

	class OriginAccessor {

		protected $precedingCallDetails, $exceptionDetector, $objectMeta,

		$container, $typeSetter, $objectCaller;

		public function __construct (DetectedExceptionManager $exceptionDetector, ObjectDetails $objectMeta, BuiltInType $typeSetter) {

			$this->exceptionDetector = $exceptionDetector;

			$this->objectMeta = $objectMeta;

			$this->typeSetter = $typeSetter;
		}

		protected function triggerOrigin (string $method, array $arguments) {

			if (!$this->precedingCallDetails->calledConcrete()) // prevent double calls

				return call_user_func_array(
				
					[$this->precedingCallDetails, $method], $arguments
				);

			return $this->precedingCallDetails->getResult(); // ?
		}

		public function setCallDetails (DecoratorCallResult $details, string $caller):void {

			$this->precedingCallDetails = $details;

			$this->objectCaller = $caller;
		}

		public function getCallDetails ():DecoratorCallResult {

			return $this->precedingCallDetails;
		}

		public function getServiceCaller ():string {

			$this->objectCaller;
		}

		protected function attemptDiffuse (Throwable $exception, string $method):OptionalDTO {

			$this->exceptionDetector->detonateOrDiffuse($exception, $this->precedingCallDetails);

			$callerResponse = $this->precedingCallDetails->failureState($method);

			return $callerResponse ?? $this->buildFailureContent($method);
		}

		private function buildFailureContent (string $method):OptionalDTO {

			$returnType = $this->objectMeta->methodReturnType(

				get_class($this->precedingCallDetails), $method
			);

			if ( (new ReflectionType($returnType))->isBuiltin())

				$typeDummy = $this->typeSetter->getDefaultValue($returnType);

			else $typeDummy = $this->container->getClass($returnType); // replace with null proxy

			return new OptionalDTO($typeDummy, false );
		}
	}
?>