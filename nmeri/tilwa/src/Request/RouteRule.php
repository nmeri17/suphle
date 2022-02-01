<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Auth\AuthStorage;

	abstract class RouteRule {

		protected $authStorage;

		private $patterns; // we wanna update this on each iteration or on the general evaluation

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		abstract public function permit ():bool;

		public function hasPattern (string $pattern):bool {

			return in_array($pattern, $this->patterns);
		}
	}
?>