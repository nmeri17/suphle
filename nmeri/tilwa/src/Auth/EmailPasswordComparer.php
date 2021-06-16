<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\{AuthStorage, UserHydrator};

	use Tilwa\Routing\RequestDetails;

	class EmailPasswordComparer {

		private $userHydrator, $requestDetails, $user;

		public function __construct (UserHydrator $userHydrator, RequestDetails $requestDetails) {

			$this->userHydrator = $userHydrator;

			$this->requestDetails = $requestDetails;
		}

		public function setAuthMechanism (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function compare ():bool {

			$payload = $this->requestDetails->getPayload();

			$user = $this->userHydrator->findAtLogin();

			if (
				is_null($user) ||

				!password_verify($payload["password"], $user->password)
			)

				return false;

			$this->user = $user;

			return true;
		}

		public function getUser () {

			return $this->user;
		}
	}
?>