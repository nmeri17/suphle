<?php
	namespace Tilwa\Middleware;

	class MiddlewareRegistry {

		private $registry = [], // [patternName => PatternMiddleware]

		$excludePatterns = [], $interactedPatterns = [];

		public function tagPatterns (array $patterns, array $middlewares):self {

			$uniquePatterns = array_unique($patterns);

			$uniqueMiddlewares = array_unique($middlewares);

			foreach ($uniquePatterns as $pattern) {

				if (array_key_exists($pattern, $this->registry))

					$context = $this->registry[$pattern];

				else $context = $this->registry[$pattern] = new PatternMiddleware;

				foreach ($uniqueMiddlewares as $instance)

					$context->addMiddleware($instance);
			}

			return $this;
		}

		public function updateInteractedPatterns (string $pattern):void {

			$this->interactedPatterns[] = $pattern;
		}

		/**
		 * These will ultimately be detached from whatever route is active
		 * 
		 * @param {parentTags} Middlewares previously tagged while descending the route collections to the point where this is called
		 * 
		 * @param {patterns} If any of these turns out to be the active pattern, [parentTag] will be detached
		*/
		public function removeTag (array $patterns, array $parentTags):self {

			foreach ($patterns as $pattern) {

				if (!array_key_exists($pattern, $this->excludePatterns))

					$this->excludePatterns[$pattern] = [];

				$this->excludePatterns[$pattern] = array_merge($this->excludePatterns[$pattern], $patterns);
			}

			return $this;
		}

		/**
		 * @return PatternMiddleware[]
		*/
		public function getActiveStack ():array {

			$activeHolders = array_filter ($this->registry, function ($pattern) {

				return in_array($pattern, $this->interactedPatterns);
			}, ARRAY_FILTER_USE_KEY);

			foreach ($activeHolders as $pattern => $holder)

				if (array_key_exists($pattern, $this->excludePatterns))

					$this->extractFromHolders($holder, $this->excludePatterns[$pattern]);

			return $activeHolders;
		}

		protected function extractFromHolders (PatternMiddleware $holder, array $toOmit):void {

			foreach ($toOmit as $middleware)

				$holder->omitWherePresent($middleware);
		}

		public function emptyAllStacks ():void {

			$this->interactedPatterns = [];

			$this->excludePatterns = [];

			$this->registry = [];
		}
	}
?>