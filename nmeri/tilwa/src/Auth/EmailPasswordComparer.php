<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\{AuthStorage, UserHydrator};

	use Tilwa\Routing\PayloadStorage;

	class EmailPasswordComparer {

		private $userHydrator, $payloadStorage, $user;

		public function __construct (UserHydrator $userHydrator, PayloadStorage $payloadStorage) {

			$this->userHydrator = $userHydrator;

			$this->payloadStorage = $payloadStorage;
		}

		public function setAuthMechanism (AuthStorage $authStorage) {

			$this->authStorage = $authStorage;
		}

		public function compare ():bool {

			$payload = $this->payloadStorage->fullPayload();

			$user = $this->userHydrator->findAtLogin();

			if (
				is_null($user) ||

				!password_verify($payload["password"], $user->getPassword())
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