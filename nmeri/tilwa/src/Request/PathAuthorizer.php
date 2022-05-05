<?php
	namespace Tilwa\Request;

	use Tilwa\Hydration\Container;

	class PathAuthorizer {

		private $container, $allRules = [],

		$interactedPatterns = [], $excludeRules = [];

		public function __construct (Container $container) {

			$this->container = $container;
		}

		public function addRule (array $patterns, string $rule):self {

			$this->createAndInclude($this->allRules, $patterns, $rule);

			return $this;
		}

		private function createAndInclude (array &$context, array $patterns, string $rule):void {

			if (!array_key_exists($rule, $context))

				$context[$rule] = [];

			$context[$rule] = array_merge($context[$rule], $patterns);
		}

		/**
		 * [1 => A], later, calling this on [1/b => unset 1]
		*/
		public function forgetRule (array $patterns, string $rule):self {

			$this->createAndInclude($this->excludeRules, $patterns, $rule);

			return $this;
		}

		public function forgetAllRules ():void {

			$this->excludeRules = [];

			$this->allRules = [];
		}

		public function updateRuleStatus (string $pattern):void {

			$this->interactedPatterns[] = $pattern;
		}

		public function passesActiveRules ():bool {

			$activeRules = array_filter($this->allRules, function ($patterns) {

				return !empty(array_intersect($this->interactedPatterns, $patterns)) &&

				empty(array_intersect($this->excludeRules, $patterns));
			});

			foreach ($activeRules as $rule => $patterns) {

				if (!$this->container->getClass($rule)->permit())

					return false;
			}

			return true;
		}
	}
?>