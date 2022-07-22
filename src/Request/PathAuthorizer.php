<?php
	namespace Suphle\Request;

	use Suphle\Hydration\Container;

	class PathAuthorizer {

		private $container, $allRules = [], // [ruleName => [taggedPatterns]]

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

			foreach ($this->getActiveRules() as $rule => $patterns) {

				if (!$this->container->getClass($rule)->permit())

					return false;
			}

			return true;
		}

		public function getActiveRules ():array {

			$ruleAllPatterns = []; // all patterns active under each rule regardless whether they were tagged or not

			foreach ($this->allRules as $ruleName => $patterns) {

				$didInteract = !empty(array_intersect(

					$this->interactedPatterns, $patterns
				));

				if ($didInteract)

					$ruleAllPatterns[$ruleName] = array_merge($this->interactedPatterns, $patterns); // since not all interacted are explicitly tagged i.e. child patterns, if they're not merged, the exclusion loop will have no way of knowing whether or not to exclude an interacted pattern that wasn't tagged
			}

			$acceptedRules = [];

			foreach ($ruleAllPatterns as $ruleHandler => $patterns) { // untag

				if (
					!array_key_exists($ruleHandler, $this->excludeRules) ||
					empty(array_intersect(

						$patterns, $this->excludeRules[$ruleHandler]
					))
				)

					$acceptedRules[$ruleHandler] = $patterns;
			}

			return $acceptedRules;
		}
	}
?>