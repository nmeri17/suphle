<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\AuthStorage;

	class SessionStorage implements AuthStorage {

		private $identifier = "tilwa_user_id";

		public function setIdentifier (string $value):void {

			$_SESSION[$this->identifier] = $value;
		}

		public function getIdentifier (string $value):string {

			return $_SESSION[$this->identifier];
		}
	}
?>