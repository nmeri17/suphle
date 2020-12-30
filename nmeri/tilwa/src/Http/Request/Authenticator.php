<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\Orm;

	// handles login, logout, continuity i.e. pulling user out of orm
	class Authenticator {

		public $user;

		private $authenticationStatus = 0;

		private $databaseAdapter;

		function __construct(Orm $databaseAdapter) {

			$this->databaseAdapter = $databaseAdapter;
		}

		public function setUser () {

			$headers = getallheaders();

			$isToken = false;

			$headerKey = "Authorization";

			$identifier = null;

			if (array_key_exists($headerKey, $headers)) {

				$identifier = $headers[$headerKey];

				$isToken = true;
			}
			else $identifier = $_SESSION['tilwa_user_id'];

			if (!$identifier) $this->user = null;

			else {
				// if ($isToken) // deserialize and assign user id to identifier

				$this->user = $this->databaseAdapter->findOne(User::class, $identifier);
			}
			$this->authenticationStatus = 1; // this still needs attention
		}

		public function getUser () {

			if ( $this->authenticationStatus === 0)

				$this->setUser();

			return $this->user; // clear this on logout
		}
	}
?>