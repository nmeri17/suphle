<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\Orm;

	use Models\User;

	// handles login, logout, continuity i.e. pulling user out of orm
	class Authenticator {

		private $user;

		private $userSearched;

		private $databaseAdapter;

		function __construct(Orm $databaseAdapter) {

			$this->databaseAdapter = $databaseAdapter;

			$this->userSearched = 0;
		}

		public function continueSession ():void {

			$headers = getallheaders();

			$headerKey = "Authorization";

			$user = $identifier = null;

			if (array_key_exists($headerKey, $headers)) {

				$identifier = $headers[$headerKey]; // deserialize and assign user id to identifier
			}
			else $identifier = $_SESSION['tilwa_user_id'];

			if ($identifier)

				$user = $this->databaseAdapter->findOne(User::class, $identifier);

			$this->setUser($user);
			
			$this->userSearched = 1;
		}

		public function getUser ():User {

			if ( $this->userSearched === 0)

				$this->continueSession();

			return $this->user; // clear this on logout
		}

		public function setUser (User $user) {

			$this->user = $user;
		}
	}
?>