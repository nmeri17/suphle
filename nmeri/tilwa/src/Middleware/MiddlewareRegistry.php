<?php
	namespace Tilwa\Middleware;

	class MiddlewareRegistry {

		private $registry = [], // [patternName => PatternMiddleware]

		$excludePatterns = [];

		protected $activeStack = []; // same as [registry]

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

		/**
		 * Used to update the stack of the given pattern; ostensibly, there's been some changes to the registry (most likely during route finding) since its initialization
		*/
		public function updateStack (string $pattern):void {

			$this->filterParents();

			$this->overwriteInActiveStack($pattern);
		}

		private function filterParents ():void {

			$toExclude = array_intersect_key($this->activeStack, $this->excludePatterns);

			foreach ($toExclude as $parent)

				unset($this->activeStack[$parent]);
		}

		private function overwriteInActiveStack (string $pattern):void {

			if (array_key_exists($pattern, $this->registry)) // we're making sure this isn't just any pattern, but one that has been tagged previously

				$this->activeStack[$pattern] = $this->registry[$pattern];
		}

		/**
		 * These will ultimately be detached from whatever route is active
		 * 
		 * @param {parent} A pattern that has previously been tagged/assigned middlewares while descending the route collections to the point where this is called
		 * 
		 * @param {patterns} If any of these turns out to be the active pattern, [parent]'s middlewares will be detached
		*/
		public function removeTag (array $patterns, string $parent):self {

			if (!array_key_exists($parent, $this->excludePatterns))

				$this->excludePatterns[$parent] = [];

			$this->excludePatterns[$parent] = array_merge($this->excludePatterns[$parent], $patterns);

			return $this;
		}

		/**
		 * @return PatternMiddleware[]
		*/
		public function getActiveStack ():array {

			return $this->activeStack;
		}

		public function emptyAllStacks ():void {

			$this->activeStack = [];

			$this->registry = [];
		}
	}
?>