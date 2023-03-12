<?php
	namespace Suphle\Middleware;

	use Suphle\Services\Decorators\BindsAsSingleton;

	#[BindsAsSingleton]
	class MiddlewareRegistry {

		protected array $registry = [],

		$excludePatterns = [], $interactedPatterns = [];

		public function tagPatterns (MiddlewareCollector $collector):self {

			$this->registry[] = $collector;

			return $this;
		}

		public function updateInteractedPatterns (string $pattern):void {

			$this->interactedPatterns[] = $pattern;
		}

		public function removeTag (

			array $patternsToOmit, callable $matcher
		):self {

			foreach ($patternsToOmit as $pattern)

				$this->excludePatterns[$pattern] = $matcher;

			return $this;
		}

		public function getRoutedCollectors ():array {

			$stack = [];

			foreach ($this->interactedPatterns as $pattern) {

				$stack = array_filter(

					$this->registry, function (MiddlewareCollector $collector) use ($pattern) {

						if (!$collector->containsPattern($pattern))

							return false;

						if (!array_key_exists($pattern, $this->excludePatterns))

							return true;

						return $this->excludePatterns[$pattern]($collector);
				});
			}

			return $stack;
		}

		public function emptyAllStacks ():void {

			$this->interactedPatterns = [];

			$this->excludePatterns = [];

			$this->registry = [];
		}
	}
?>