<?php
	namespace Tilwa\Middleware;

	use Tilwa\Response\ResponseManager;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Controllers\ControllerManager;

	use Tilwa\Contracts\Middleware;

	class MiddlewareQueue {

		private $manager, $router, $controllerManager;

		public function __construct (ResponseManager $manager, RouteManager $router, ControllerManager $controllerManager) {

			$this->responseManager = $manager;

			$this->router = $router;

			$this->controllerManager = $controllerManager;
		}

		/**
		 * Convert a path foo/bar with stack
		 * foo => patternMiddleware([1,2])
		 * bar => patternMiddleware([1,3]) to [1,2,3]
		*/
		public function getUniqueMiddleware (array $middlewareStack):array {

			$units = array_map(function (PatternMiddleware $stack) {

				return $stack->getList();
			}, $middlewareStack);

			$reduced = array_reduce($units, function (array $carry, array $current) {

				$carry += $current;

				return $carry;
			}, []);

			$uniqueNames = [];

			return array_filter($reduced, function (Middleware $middleware) use (&$uniqueNames) {

				$name = get_class($middleware);

				if (!in_array($name, $uniqueNames))

					return false;

				$uniqueNames[] = $name;

				return true;
			});
		}

		// this should return ResponseInterface according to psr-15
		public function runStack ():string {

			$stack = $this->router->getPatternMiddleware();

			$stack = $this->getUniqueMiddleware($stack);

			array_unshift($stack, new FinalHandlerWrapper($this->responseManager));

			return end($stack)->process(
				$this->controllerManager->getRequest(),

				$this->getHandlerChain($stack)
			);
		}

		// convert each middleware to a request interface carrying the previous one so triggering each one creates a chain effect till the last one
		private function getHandlerChain (array $middlewareList, MiddlewareNexts $accumNexts):MiddlewareNexts {

			if (empty($middlewareList)) return $accumNexts;

			$nextHandler = new MiddlewareNexts(array_shift($middlewareList), $accumNexts);

			// [1,2,4] => [4(2(1(cur, null), cur), cur)]
			/* [1,2,4] => 1,[2,4]
			[2,4] => 2,[4]
			[4] = each level injests its predecessor
			*/
			return $this->getHandlerChain($middlewareList, $nextHandler);
		}
	}
?>