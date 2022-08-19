<?php
	namespace Suphle\Services\DecoratorHandlers;

	use Suphle\Contracts\Services\Decorators\ServiceErrorCatcher;

	use Suphle\Contracts\Config\DecoratorProxy;

	use Suphle\Exception\DetectedExceptionManager;

	use Suphle\Hydration\Structures\ObjectDetails;

	use ProxyManager\{Factory\NullObjectFactory, Proxy\AccessInterceptorInterface};

	use ReflectionClass, Throwable;

	/**
	 * Any decorator composed of this handler must extend ServiceErrorCatcher
	*/
	class ErrorCatcherHandler extends BaseInjectionModifier {

		private $exceptionDetector;

		public function __construct (
			DetectedExceptionManager $exceptionDetector, ObjectDetails $objectMeta,
			DecoratorProxy $proxyConfig) {

			$this->exceptionDetector = $exceptionDetector;

			parent::__construct($proxyConfig, $objectMeta);
		}

		/**
		 * {@inheritdoc}
		*/
		public function examineInstance (object $concrete, string $caller):object {

			return $this->allMethodAction($concrete,

				[$this, "safeCallMethod"]
			);
		}

		/**
		 * @param {proxy} Object received by the caller. Any changes that will be read at that end or mutation expected to be performed on the object should be done on this object. But under no circumstance should {methodName} be invoked on it as that will launch a recursive loop
		*/
		public function safeCallMethod (
			AccessInterceptorInterface $proxy, object $concrete,

			string $methodName, array $argumentList
		) {

			try {

				return $this->triggerOrigin($concrete, $methodName, $argumentList);
			}
			catch (Throwable $exception) {

				return $this->attemptDiffuse($exception, $proxy, $concrete, $methodName);
			}
		}

		public function attemptDiffuse (
			Throwable $exception, AccessInterceptorInterface $proxy,

			ServiceErrorCatcher $concrete, string $method
		) {

			$this->exceptionDetector->detonateOrDiffuse($exception, $concrete);

			$proxy->didHaveErrors($method);

			$callerResponse = $concrete->failureState($method);

			return $callerResponse ??

			$this->buildFailureContent($concrete, $method);
		}

		/**
		 * @return A base value matching return type of method in question
		*/
		private function buildFailureContent (ServiceErrorCatcher $concrete, string $method) {

			$objectMeta = $this->objectMeta;

			$returnType = $objectMeta->methodReturnType(

				get_class($concrete), $method
			);

			if (is_null($returnType)) return null;
			
			if ( $objectMeta->returnsBuiltIn(get_class($concrete), $method))

				return $objectMeta->getScalarValue($returnType);

			return (new NullObjectFactory(

				$this->proxyConfig->getConfigClient()
			))->createProxy($returnType);
		}
	}
?>