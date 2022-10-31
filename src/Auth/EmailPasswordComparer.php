<?php
	namespace Suphle\Auth;

	use Suphle\Contracts\Database\OrmDialect;

	use Suphle\Contracts\Auth\{UserContract, ColumnPayloadComparer};

	use Suphle\Request\PayloadStorage;

	class EmailPasswordComparer implements ColumnPayloadComparer {

		private $userHydrator, $payloadStorage, $user;

		protected $columnIdentifier = "email";

		public function __construct (OrmDialect $ormDialect, PayloadStorage $payloadStorage) {

			$this->userHydrator = $ormDialect->getUserHydrator();

			$this->payloadStorage = $payloadStorage;
		}

		protected function findMatchingUser ():?UserContract {

			return $this->userHydrator->findAtLogin([

				$this->columnIdentifier => $this->payloadStorage->getKey($this->columnIdentifier)
			]);
		}

		public function compare ():bool {

			$user = $this->findMatchingUser();

			$password = $this->payloadStorage->getKey("password");

			if (
				is_null($user) ||

				!password_verify((string) $password, (string) $user->getPassword())
			)

				return false;

			$this->user = $user;

			return true;
		}

		public function getUser ():UserContract {

			return $this->user;
		}
	}
?>