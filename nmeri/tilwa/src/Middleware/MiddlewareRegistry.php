<?php
	namespace Tilwa\Middleware;

	class MiddlewareRegistry {

		private $registry = [];

		public function tagPatterns (array $patterns, array $middlewares) {

			foreach ($patterns as $pattern) {

				if (array_key_exists($pattern, $this->registry))

					$context = $this->registry[$pattern];

				else $context = $this->registry[$pattern] = new PatternMiddleware;

				foreach ($middlewares as $instance)

					$context->addMiddleware($instance);
			}
		}

		public function getStack (string $pattern):PatternMiddleware {

			if (array_key_exists($pattern, $this->registry))

				return $this->registry[$pattern];
		}
	}
?>