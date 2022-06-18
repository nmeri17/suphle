<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Auth\AuthStorage;

	abstract class RouteRule {

		protected $authStorage;

		public function __construct (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		abstract public function permit ():bool;
	}
?>