<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Modules\HighLevelRequestHandler, Config\ExceptionInterceptor, Presentation\BaseRenderer};

	use Tilwa\Hydration\Container;

	use Tilwa\Response\Format\AbstractRenderer;

	use Tilwa\Request\PayloadStorage;

	use Throwable, Exception;

	class ModuleExceptionBridge implements HighLevelRequestHandler {

		private $container, $handler, $config,

		$fatalExceptions = [E_ERROR, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING],

		$payloadStorage;

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

		public function handlingRenderer ():BaseRenderer {

			$this->handler->prepareRendererData();

			return $this->handler->getRenderer();
		}

		public function epilogue ():void {

			ini_set("display_errors", false); // prevent error from flashing at user

			register_shutdown_function(function () {

				$lastError = error_get_last();

				$isFatal = !is_null($lastError) && in_array($lastError["type"], $this->fatalExceptions);

				if (!$isFatal ) return;

				$this->handler = $this->container->getClass($this->config->defaultHandler());

				$exception = new Exception(json_encode($lastError)); // will have no trace
				
				$this->handler->setContextualData($exception);

				$this->exceptionManager->queueAlertAdapter($exception, $this->payloadStorage);

				$renderer = $this->handlingRenderer();

				http_response_code($renderer->getStatusCode());

				echo $renderer->render();
			});
		}
	}
?>