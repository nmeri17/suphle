<?php
	namespace Suphle\Middleware;

	use Suphle\Hydration\Container;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\{Presentation\BaseRenderer, Config\Router as RouterConfig};

	class MiddlewareQueue {

		protected array $mergedStack = [], $routedCollectors = [];

		public function __construct (

			MiddlewareRegistry $registry,

			protected readonly PayloadStorage $payloadStorage,

			protected readonly RouterConfig $routerConfig,

			protected readonly Container $container
		) {

			$this->routedCollectors = $registry->getRoutedFunnels();
		}

		public function runStack ():BaseRenderer {

			if (empty($this->mergedStack)) $this->setMergedStack();

			$mergedStack = $this->mergedStack; // copy to avoid mutating the main stack with array_shift

			$outermost = array_shift($mergedStack);

			return $outermost->process(
				$this->payloadStorage,

				$this->getHandlerChain($mergedStack)
			);
		}

		protected function setMergedStack ():void  {
			
			$routedHandlers = [];

			foreach ($this->routedCollectors as $collector) {

				$handlerName = $this->routerConfig->collectorHandlers()[$collector::class];

				$handler = $this->container->getClass($handlerName); // since multiple instances can exist for those patterns down the collection trie, a handler may be called up more than once, as well

				$handler->addMetaFunnel($collector);

				$routedHandlers[] = $handler;
			}

			$this->mergedStack = array_merge( // any temporary ones attached to route precede the defaults
				$routedHandlers,

				array_map(

					fn(string $name) => $this->container->getClass($name),

					$this->routerConfig->defaultMiddleware()
				)
			);
		}

		/**
		 *  convert each middleware to a request interface carrying the next one so triggering each one creates a chain effect till the last one
		 * @param {accumNexts} null for the final handler since there's none below it
		 * @return null for the last handler in the chain
		*/
		private function getHandlerChain (array $middlewareList, MiddlewareNexts $accumNexts = null):?MiddlewareNexts {

			if (empty($middlewareList)) return $accumNexts;
			
			$lastMiddleware = array_pop($middlewareList); // we're reading from behind so that last item on the list is what is passed to the caller, and thus, is first to be evaluated on our way down the rabbit hole

			$nextHandler = new MiddlewareNexts($lastMiddleware, $accumNexts);

			// [1,2,4] => [4(2(1(cur, null), cur), cur)]
			/* [1,2,4] => 1,[2,4]
			[2,4] => 2,[4]
			[4] = each level injests its predecessor
			*/
			return $this->getHandlerChain($middlewareList, $nextHandler);
		}
	}
?>