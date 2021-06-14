<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\AuthStorage;

	class EmailPasswordComparer {

		public function __construct (Orm $orm, RequestDetails $requestDetails) {

			$this->orm = $orm;

			$this->requestDetails = $requestDetails;
		}

		public function setAuthMechanism (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function compare ():bool {

			// string $email, string $password
			//if ($this->requestDetails)

			// if requestDetails matches email, compare password. then update sessionStorage
		}
	}
?>