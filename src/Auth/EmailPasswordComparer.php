<?php
	namespace Suphle\Auth;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Request\PayloadStorage;

	class EmailPasswordComparer {

		private $userHydrator, $payloadStorage, $user;

		public function __construct (OrmDialect $ormDialect, PayloadStorage $payloadStorage) {

			$this->userHydrator = $ormDialect->getUserHydrator();

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