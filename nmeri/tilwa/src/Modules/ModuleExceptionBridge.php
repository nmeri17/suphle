<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{Modules\HighLevelRequestHandler, Config\ExceptionInterceptor};

	use Tilwa\Hydration\Container;

	use Tilwa\Response\Format\AbstractRenderer;

	use Throwable;

	class ModuleExceptionBridge implements HighLevelRequestHandler {

		private $container, $handler, $config;

		public function __construct( Container $container, ExceptionInterceptor $config) {

			$this->container = $container;

			$this->config = $config;
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

		public function handlingRenderer ():AbstractRenderer {

			$this->handler->prepareRendererData();

			return $this->handler->getRenderer();
		}
	}
?>