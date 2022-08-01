<?php
	namespace Suphle\Middleware;

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
		 * @param {patterns} If any of these turns out to be among active patterns, this list of [parentTags] will be detached
		*/
		public function removeTag (array $patterns, array $parentTags):self {

			foreach ($patterns as $pattern) {

				if (!array_key_exists($pattern, $this->excludePatterns))

					$this->excludePatterns[$pattern] = [];

				$this->excludePatterns[$pattern] = array_merge($this->excludePatterns[$pattern], $parentTags);
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

			// If we exclude tags in a child collection, it won't exist in [registry] since our remove action doesn't use such mechanism
			$activeExcludes = array_filter ($this->excludePatterns, function ($pattern) {

				return in_array($pattern, $this->interactedPatterns);
			}, ARRAY_FILTER_USE_KEY);

			// search among parents for those containing middlewares intersecting with given list
			foreach ($activeExcludes as $excludeList)
			
				foreach ($activeHolders as $holder)

					$holder->omitWherePresent($excludeList);

			return $activeHolders;
		}

		public function emptyAllStacks ():void {

			$this->interactedPatterns = [];

			$this->excludePatterns = [];

			$this->registry = [];
		}
	}
?>