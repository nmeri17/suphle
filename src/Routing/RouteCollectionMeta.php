<?php
	namespace Suphle\Routing;

	use Suphle\Routing\Structures\CollectionMetaExclusion;

	abstract class RouteCollectionMeta {

		protected array $registry = [],

		$excludePatterns = [], $interactedPatterns = [];

		public function tagPatterns (CollectionMetaFunnel $collector):self {

			$this->registry[] = $collector;

			return $this;
		}

		public function updateInteractedPatterns (string $pattern):void {

			$this->interactedPatterns[] = $pattern;
		}

		public function removeTag (

			array $patternsToOmit, string $funnelName, callable $matcher = null
		):self {

			foreach ($patternsToOmit as $pattern)

				$this->excludePatterns[$pattern] = new CollectionMetaExclusion($funnelName, $matcher);

			return $this;
		}

		/**
		 * @return CollectionMetaFunnel[] relevant to current path
		*/
		public function getRoutedFunnels ():array {

			$stack = $this->registry;

			foreach ($this->interactedPatterns as $index => $pattern) {

				$isOuter = $index == 0;

				$stack = array_filter(

					$stack, function (CollectionMetaFunnel $collector) use ($pattern, $isOuter) {

						if ($isOuter && !$collector->containsPattern($pattern)) // it's permitted to not exist on child collections i.e. if the outer one tagged it

							return false;

						if (array_key_exists($pattern, $this->excludePatterns))

							return $this->excludePatterns[$pattern]

							->shouldExclude($collector);
						
						return true;
					}
				);
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