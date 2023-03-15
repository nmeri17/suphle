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

			$toWeedOut = array_intersect(

				$this->interactedPatterns,

				array_keys($this->excludePatterns)
			);

			return array_filter($this->registry, function (CollectionMetaFunnel $funnel) use ($toWeedOut) {

				$boundToInteracted = false;

				foreach ($this->interactedPatterns as $pattern) {

					if ($funnel->containsPattern($pattern)) {

						$boundToInteracted = true;

						break;
					}
				}

				if (!$boundToInteracted) return false;

				foreach ($toWeedOut as $pattern) {

					$shouldExclude = $this->excludePatterns[$pattern]

					->shouldExclude($funnel);

					if ($shouldExclude) return false;
				}

				return true;
			});
		}

		public function emptyAllStacks ():void {

			$this->interactedPatterns = [];

			$this->excludePatterns = [];

			$this->registry = [];
		}
	}
?>