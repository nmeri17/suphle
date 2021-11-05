<?php
	namespace Tilwa\Middleware;

	use Tilwa\App\Container;

	use Tilwa\Routing\RequestDetails;

	use Tilwa\Contracts\{Middleware, Router as RouterConfig};

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
		 * foo => patternMiddleware([1,2])
		 * bar => patternMiddleware([1,3]) to [1,2,3]
		*/
		private function filterDuplicates ():self {

			$units = array_map(function (PatternMiddleware $stack) {

				return $stack->getList();
			}, $this->stack);

			$reduced = array_reduce($units, function (array $carry, array $current) {

				$carry = array_merge($carry, $current);

				return $carry;
			}, []);

			$uniqueNames = [];

			$this->stack = array_filter($reduced, function (Middleware $middleware) use (&$uniqueNames) {

				$name = get_class($middleware);

				if (!in_array($name, $uniqueNames))

					return false;

				$uniqueNames[] = $name;

				return true;
			});

			return $this;
		}

		// this should return ResponseInterface according to psr-15
		public function runStack ():string {

			$this->filterDuplicates()->prependDefaults();

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

		private function prependDefaults ():self {

			$defaults = array_map(function ($name) {

				return $this->container->getClass($name);

			}, $this->routerConfig->defaultMiddleware());

			$this->stack = [...$defaults, ...$this->stack];

			return $this;
		}
	}
?>