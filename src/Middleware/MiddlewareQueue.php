<?php
	namespace Suphle\Middleware;

	use Suphle\Hydration\Container;

	use Suphle\Request\PayloadStorage;

	use Suphle\Contracts\{Presentation\BaseRenderer, Config\Router as RouterConfig};

	class MiddlewareQueue {

		private array $routedStack, $mergedStack = [];

		public function __construct (

			MiddlewareRegistry $registry,

			private readonly PayloadStorage $payloadStorage,

			private readonly RouterConfig $routerConfig,

			private readonly Container $container
		) {

			$this->routedStack = $registry->getActiveStack();
		}

		/**
		 * Convert a path foo/bar with stack
		 * foo => patternMiddleware([middleware1,middleware2])
		 * bar => patternMiddleware([middleware1,middleware3]) to [middleware1,middleware2,middleware3]
		*/
		private function filterDuplicates ():void {

			$units = array_map(fn(PatternMiddleware $pattern) => $pattern->getList(), $this->routedStack);

			$reduced = array_reduce($units, function (array $carry, array $current) {

				$carry = array_merge($carry, $current);

				return $carry;
			}, []);

			$this->routedStack = array_unique($reduced);
		}

		public function runStack ():BaseRenderer {

			if (empty($this->mergedStack)) { // purpose of this 2nd stack is in long-running settings e.g. Flows, this object will be retained. Routing only happens once per pattern, so if the original stack is overwritten, subsequent Flow requests for that pattern will have undesirable behavior

				$this->filterDuplicates();

				$this->mergedStack = array_map(

					fn(string $name) => $this->container->getClass($name),

					array_merge( // any temporary ones attached to route precede the defaults
						$this->routedStack,

						$this->routerConfig->defaultMiddleware()
					)
				);
			}

			$mergedStack = $this->mergedStack; // copy to avoid mutating the main stack below

			$outermost = array_shift($mergedStack);

			return $outermost->process(
				$this->payloadStorage,

				$this->getHandlerChain($mergedStack)
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