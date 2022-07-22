<?php
	namespace Suphle\Middleware;

	use Suphle\Hydration\Container;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\{Presentation\BaseRenderer, Config\Router as RouterConfig};

	class MiddlewareQueue {

		private $payloadStorage, $stack, $routerConfig, $container;

		public function __construct ( MiddlewareRegistry $registry, PayloadStorage $payloadStorage, RouterConfig $routerConfig, Container $container) {

			$this->stack = $registry->getActiveStack();

			$this->payloadStorage = $payloadStorage;

			$this->routerConfig = $routerConfig;

			$this->container = $container;
		}

		/**
		 * Convert a path foo/bar with stack
		 * foo => patternMiddleware([middleware1,middleware2])
		 * bar => patternMiddleware([middleware1,middleware3]) to [middleware1,middleware2,middleware3]
		*/
		private function filterDuplicates ():void {

			$units = array_map(function (PatternMiddleware $pattern) {

				return $pattern->getList();
			}, $this->stack);

			$reduced = array_reduce($units, function (array $carry, array $current) {

				$carry = array_merge($carry, $current);

				return $carry;
			}, []);

			$this->stack = array_unique($reduced);
		}

		public function runStack ():BaseRenderer {

			$this->filterDuplicates();

			$this->stack = array_merge( // any temporary ones attached to route precede the defaults
				$this->stack,

				$this->routerConfig->defaultMiddleware()
			);

			$this->hydrateMiddlewares();

			$outermost = array_shift($this->stack);

			return $outermost->process(
				$this->payloadStorage,

				$this->getHandlerChain($this->stack)
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

		private function hydrateMiddlewares ():void {

			$this->stack = array_map(function ($name) {

				return $this->container->getClass($name);
			}, $this->stack);
		}
	}
?>