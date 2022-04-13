<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Modules\HighLevelRequestHandler, Config\ExceptionInterceptor, Presentation\BaseRenderer};

	use Tilwa\Hydration\Container;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Request\PayloadStorage;

	use Throwable, Exception;

	class ModuleExceptionBridge implements HighLevelRequestHandler {

		private $container, $handler, $config, $payloadStorage,

		$wasHandlingShutdownError = false;

		public function __construct( Container $container, ExceptionInterceptor $config, PayloadStorage $payloadStorage) {

			$this->container = $container;

			$this->config = $config;

			$this->payloadStorage = $payloadStorage;
		}

		public function hydrateHandler (Throwable $exception) {

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

			return $this->handler->getRenderer();
		}

		public function epilogue ():void {

			ini_set("display_errors", false); // prevent error from flashing at user

			register_shutdown_function(function () {

				$lastError = error_get_last();

				if ( is_null($lastError) ) return; // no error. Just end of request

				$stringifiedError = json_encode($lastError);

				if ($this->wasHandlingShutdownError) {

					echo $this->disgracefulShutdown($stringifiedError);

					return;
				}

				$this->wasHandlingShutdownError = true;

				echo $this->gracefulShutdown($stringifiedError);
			});
		}

		/**
		 * The one place we never wanna be
		*/
		public function disgracefulShutdown (string $errorDetails):string {

			file_put_contents($this->config->shutdownLog(), $errorDetails, FILE_APPEND);

			$this->writeStatusCode(500); // send mail and at leat print somethin
		}

		public function gracefulShutdown (string $errorDetails):string {

			$handler = $this->container->getClass($this->config->defaultHandler());

			$exception = new Exception($errorDetails); // this means this will have a fake trace
			
			$handler->setContextualData($exception);

			$this->exceptionManager->queueAlertAdapter($exception, $this->payloadStorage);

			$renderer = $this->handlingRenderer();

			$renderer->hydrateDependencies($this->container);

			$this->writeStatusCode($renderer->getStatusCode());

			return $renderer->render();
		}

		public function writeStatusCode (int $statusCode):void {

			http_response_code($statusCode);
		}
	}
?>