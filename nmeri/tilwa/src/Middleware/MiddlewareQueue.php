<?php
	namespace Tilwa\Middleware;

	class MiddlewareQueue {

		private $manager, $router;

		public function __construct (ResponseManager $manager, RouteManager $router) {

			$this->manager = $manager;

			$this->router = $router;
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

				$name = $middleware::class;

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

			$handlers = $this->getHandlerInterfaces($stack);

			$request = $this->manager->getControllerManager()->getRequest();

			return $stack[0]->handle($request, $handlers[0]); // stops after running the first one
		}

		// convert each middleware to a request interface carrying the previous one so triggering each one creates a chain effect till the last one
		private function getHandlerInterfaces (array $middlewareList) {

			$wrapped = [];

			foreach ($middlewareList as $index => $middleware) {

				if ($index >= 1) {

					$previous = $index-1;

					$wrapped[] = new MiddlewareNexts($middleware, $middlewareList[$previous]);
				}

				else $wrapped[] = new FinalHandlerWrapper($this->manager);
			}

			return $wrapped;
		}
	}
?>