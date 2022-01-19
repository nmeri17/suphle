<?php
	namespace Tilwa\Modules;

	use Tilwa\Contracts\{App\HighLevelRequestHandler, Exception\ContextualException};

	use Tilwa\Contracts\Config\ExceptionInterceptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Response\Format\AbstractRenderer;

	use Throwable;

	class ModuleExceptionBridge implements HighLevelRequestHandler {

		private $container, $handler;

		public function __construct( Container $container) {

			$this->container = $container;
		}

		public function hydrateHandler (Throwable $exception) {

			$handlers = $this->container->getClass(ExceptionInterceptor::class);

			$this->handler = $this->container->getClass($handlers[get_class($exception)]);

			if ($exception instanceof ContextualException)

				$this->handler->setData($exception->getContext());
		}

		public function handlingRenderer ():AbstractRenderer {

			$this->handler->prepareRendererData();

			return $this->handler->getRenderer();
		}
	}
?>