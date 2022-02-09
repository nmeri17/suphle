<?php
	namespace Tilwa\Request;

	use Tilwa\Contracts\Auth\AuthStorage;

	abstract class RouteRule {

		protected $authorizedUser;

		public function __construct (AuthStorage $authStorage) {

			$this->authorizedUser = $authStorage->getUser();
		}

		abstract public function permit ():bool;
	}
?>