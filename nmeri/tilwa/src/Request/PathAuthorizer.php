<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Auth\AuthStorage;

	class PathAuthorizer {

		private $authStorage, $allRules = [],

		$activeRules = [], $excludeRules = [];

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function addRule (RouteRule $rule):self {

			$this->allRules[] = $rule;

			return $this;
		}

		/*[1 => A], [1b => unset 1]*/
		public function forgetRule (array $patterns, string $rule):self {

			if (!array_key_exists($rule, $this->excludeRules))

				$this->excludeRules[$rule] = [];

			$this->excludeRules[$rule] = array_merge($this->excludeRules[$rule], $patterns);

			return $this;
		}

		public function updateRuleStatus (string $pattern):void {

			$this->setActiveRules($pattern);

			$this->detachUnwanted();
		}

		private function setActiveRules (string $pattern):void {

			$matches = array_filter($this->allRules, function (RouteRule $rule) use ($pattern) {

				$rule->setAuthStorage($this->authStorage);

				return $rule->hasPattern($pattern);
			});

			$this->activeRules = array_merge($this->activeRules, $matches);
		}

		private function detachUnwanted ():void {

			$this->activeRules = array_filter($this->activeRules, function (RouteRule $rule) {

				return !array_key_exists(get_class($rule), $this->excludeRules);
			});
		}

		public function getActiveRules ():array {

			return $this->activeRules;
		}
	}
?>