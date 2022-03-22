<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\Auth\UserHydrator;

	use Tilwa\Routing\PayloadStorage;

	class EmailPasswordComparer {

		private $userHydrator, $payloadStorage, $user;

		public function __construct (UserHydrator $userHydrator, PayloadStorage $payloadStorage) {

			$this->userHydrator = $userHydrator;

			$this->payloadStorage = $payloadStorage;
		}

		public function compare ():bool {

			$password = $this->payloadStorage->getKey("password");

			$user = $this->userHydrator->findAtLogin();

			if (
				is_null($user) ||

				!password_verify($password, $user->getPassword())
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