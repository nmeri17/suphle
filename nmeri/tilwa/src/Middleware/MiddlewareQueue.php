<?php
	namespace Tilwa\Middleware;

	class MiddlewareQueue {

		public function getUnique (/*patternMiddlewares*/):array {

			// something along these lines

			foreach ($this->middlewareList as $middleware)

				if (get_class($middleware) == get_class($instance))
		}
	}
?>