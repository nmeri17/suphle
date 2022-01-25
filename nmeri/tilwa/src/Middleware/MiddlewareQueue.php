<?php
	namespace Tilwa\Middleware;

	use Tilwa\Hydration\Container;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\Config\Router as RouterConfig;

	class MiddlewareQueue {

		private $requestDetails, $stack, $routerConfig, $container;

		public function __construct ( MiddlewareRegistry $registry, RequestDetails $requestDetails, RouterConfig $routerConfig, Container $container) {

			$this->stack = $registry->getActiveStack();

			$this->requestDetails = $requestDetails;

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

		// this should return ResponseInterface according to psr-15
		public function runStack ():string {

			$this->filterDuplicates();

			$this->stack = [...$this->routerConfig->defaultMiddleware(), ...$this->stack];

			$this->hydrateMiddlewares();

			$outermost = array_pop($this->stack);

			return $outermost->process(
				$this->requestDetails,

				$this->getHandlerChain($this->stack)
			);
		}

		/**
		 *  convert each middleware to a request interface carrying the previous one so triggering each one creates a chain effect till the last one
		 * @param {accumNexts} null for the final handler since there's none below it
		 * @return null for the last handler in the chain
		*/
		private function getHandlerChain (array $middlewareList, MiddlewareNexts $accumNexts = null):?MiddlewareNexts {

			if (empty($middlewareList)) return $accumNexts;

			$nextHandler = new MiddlewareNexts(array_pop($middlewareList), $accumNexts);

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