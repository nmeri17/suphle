<?php
	namespace Tilwa\Middleware;

	class MiddlewareRegistry {

		private $registry = [], $activeStack = [],

		$excludePatterns = [];

		public function tagPatterns (array $patterns, array $middlewares):self {

			foreach ($patterns as $pattern) {

				if (array_key_exists($pattern, $this->registry))

					$context = $this->registry[$pattern];

				else $context = $this->registry[$pattern] = new PatternMiddleware;

				foreach ($middlewares as $instance)

					$context->addMiddleware($instance);
			}

			return $this;
		}

		public function updateStack (string $pattern):void {

			$this->filterParents();

			$this->pushToStack($pattern);
		}

		private function filterParents ():void {

			$toExclude = array_intersect_key($this->activeStack, $this->excludePatterns);

			foreach ($toExclude as $parent)

				unset($this->activeStack[$parent]);
		}

		// if current path was recently registered, update the stack
		private function pushToStack (string $pattern):void {

			if (array_key_exists($pattern, $this->registry))

				$this->activeStack[$pattern] = $this->registry[$pattern];
		}

		// these will ultimately be detached from whatever route is active
		public function removeTag (array $patterns, string $parent):self {

			if (!array_key_exists($parent, $this->excludePatterns))

				$this->excludePatterns[$parent] = [];

			$this->excludePatterns[$parent] = array_merge($this->excludePatterns[$parent], $patterns);

			return $this;
		}

		public function getActiveStack ():array {

			return $this->activeStack;
		}
	}
?>