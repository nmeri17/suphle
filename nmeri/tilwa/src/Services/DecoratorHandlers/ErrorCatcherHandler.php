<?php
	namespace Tilwa\Services\DecoratorHandlers;

	use Tilwa\Contracts\Services\Decorators\ServiceErrorCatcher;

	use Tilwa\Contracts\Config\DecoratorProxy;

	use Tilwa\Exception\DetectedExceptionManager;

	use Tilwa\Services\Structures\OptionalDTO;

	use Tilwa\Hydration\Structures\ObjectDetails;

	use ProxyManager\Factory\NullObjectFactory;

	use ReflectionClass, Throwable;

	/**
	 * Any decorator composed of this handler must extend ServiceErrorCatcher
	*/
	class ErrorCatcherHandler extends BaseDecoratorHandler {

		private $exceptionDetector, $objectMeta;

		public function __construct (
			DetectedExceptionManager $exceptionDetector, ObjectDetails $objectMeta,
			DecoratorProxy $proxyConfig) {

			$this->exceptionDetector = $exceptionDetector;

			$this->objectMeta = $objectMeta;

			parent::__construct($proxyConfig);
		}

		/**
		 * {@inheritdoc}
		*/
		public function examineInstance (object $concrete, string $caller):object {

			return $this->allMethodAction($concrete,

				[$this, "safeCallMethod"]
			);
		}

		public function safeCallMethod (object $concrete, string $methodName, array $argumentList) {

			try {

				return $this->triggerOrigin($concrete, $methodName, $argumentList);
			}
			catch (Throwable $exception) {

				return $this->attemptDiffuse($exception, $concrete, $methodName);
			}
		}

		public function attemptDiffuse (Throwable $exception, ServiceErrorCatcher $concrete, string $method):OptionalDTO {

			$this->exceptionDetector->detonateOrDiffuse($exception, $concrete);

			$callerResponse = $concrete->failureState($method);

			return $callerResponse ??

			$this->buildFailureContent($concrete, $method);
		}

		private function buildFailureContent (ServiceErrorCatcher $concrete, string $method):OptionalDTO {

			$objectMeta = $this->objectMeta;

			$returnType = $objectMeta->methodReturnType(

				get_class($concrete), $method
			);

			if (is_null($returnType))

				return new OptionalDTO(null, false);
			
			if ( $objectMeta->isBuiltIn(get_class($concrete), $method))

				$typeDummy = $objectMeta->getScalarValue($returnType);

			else $typeDummy = (new NullObjectFactory(

				$this->proxyConfig->getConfigClient()
			))->createProxy($returnType);

			return new OptionalDTO($typeDummy, false );
		}
	}
?>