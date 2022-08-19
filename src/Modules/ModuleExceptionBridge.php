<?php
	namespace Suphle\Modules;

	use Suphle\Hydration\Container;

	use Suphle\Request\PayloadStorage;

	use Suphle\Services\DecoratorHandlers\VariableDependenciesHandler;

	use Suphle\Exception\DetectedExceptionManager;

	use Suphle\Contracts\{Modules\HighLevelRequestHandler, Config\ExceptionInterceptor, Presentation\BaseRenderer, Exception\FatalShutdownAlert, Hydration\ClassHydrationBehavior};

	use Throwable, Exception;

	class ModuleExceptionBridge implements HighLevelRequestHandler, ClassHydrationBehavior {

		private $container, $handler, $config, $payloadStorage,

		$exceptionDetector, $handledExternally;

		public function __construct(
			Container $container, ExceptionInterceptor $config,

			PayloadStorage $payloadStorage, DetectedExceptionManager $exceptionDetector,

			VariableDependenciesHandler $variableDecorator
		) {

			$this->container = $container;

			$this->config = $config;

			$this->payloadStorage = $payloadStorage;

			$this->exceptionDetector = $exceptionDetector;

			$this->variableDecorator = $variableDecorator;
		}

		public function hydrateHandler (Throwable $exception):void {

			$handlers = $this->config->getHandlers();

			$exceptionName = get_class($exception);

			if (array_key_exists($exceptionName, $handlers))

				$handlerName = $handlers[$exceptionName];
			
			else $handlerName = $this->config->defaultHandler();

			$this->handler = $this->container->getClass($handlerName);
			
			$this->handler->setContextualData($exception);
		}

		public function handlingRenderer ():?BaseRenderer {

			$this->handler->prepareRendererData();

			return $this->variableDecorator->examineInstance(

				$this->handler->getRenderer(), ""
			);
		}

		/**
		 * That this works correctly is untestable (after ModuleHandlerIdentifier::findExceptionRenderer fails)
		*/
		public function epilogue ():void {

			register_shutdown_function(function () {

				echo $this->shutdownRites();
			});
		}

		public function shutdownRites ():?string {

			$lastError = error_get_last();

			if ( $this->isFalsePositive($lastError) || $this->handledExternally)

				return null; // no error. Just end of request

			$stringifiedError = json_encode($lastError, JSON_PRETTY_PRINT);

			try {

				return $this->gracefulShutdown($stringifiedError);
			}
			catch (Throwable $exception) {

				return $this->disgracefulShutdown($stringifiedError, $exception);
			}
		}

		protected function isFalsePositive (?array $errorDetails):bool {

			return is_null($errorDetails) ||

			in_array($errorDetails["type"], [E_NOTICE, E_USER_NOTICE]);
		}

		/**
		 * The one place we never wanna be
		*/
		public function disgracefulShutdown (string $errorDetails, Throwable $exception):string {

			$errorDetails .= json_encode($exception, JSON_PRETTY_PRINT);

			file_put_contents($this->config->shutdownLog(), $errorDetails, FILE_APPEND);

			$this->writeStatusCode(500);

			$alerter = $this->container->getClass(FatalShutdownAlert::class);

			$alerter->setErrorAsJson($errorDetails);

			$alerter->handle();

			return $this->config->shutdownText();
		}

		public function gracefulShutdown (string $errorDetails):string {

			$this->handler = $this->container->getClass($this->config->defaultHandler());

			$exception = new Exception($errorDetails); // this means this will have a fake trace
			
			$this->handler->setContextualData($exception);

			$this->exceptionDetector->queueAlertAdapter($exception, $this->payloadStorage);

			$renderer = $this->variableDecorator->examineInstance(

				$this->handlingRenderer(), ""
			);

			$this->writeStatusCode($renderer->getStatusCode());

			return $renderer->render();
		}

		public function writeStatusCode (int $statusCode):void {

			http_response_code($statusCode);
		}

		public function protectRefreshPurge ():bool {

			return true; // in tests, this is provided before PayloadStorage, which is one of its dependencies
		}

		/**
		 * Causes it not to send out alerts except for uncatchable errors
		*/
		public function successfullyHandled ():void {

			$this->handledExternally = true;
		}
	}
?>