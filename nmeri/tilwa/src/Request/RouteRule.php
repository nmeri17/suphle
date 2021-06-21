<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\AuthStorage;

	abstract class RouteRule {

		private $patterns, $authStorage;

		public function setAuthStorage (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		abstract public function permit ():bool;

		public function hasPattern (string $pattern):bool {

			return in_array($pattern, $this->patterns);
		}
	}
?>