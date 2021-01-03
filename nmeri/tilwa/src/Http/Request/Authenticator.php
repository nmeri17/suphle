<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\Orm;

	use Models\User;

	// handles login, logout, continuity i.e. pulling user out of orm
	class Authenticator {

		private $user;

		private $userSearched;

		private $databaseAdapter;

		private $statelessLogin;

		private $sessionIdentifier;

		/**
		* @param {checkToken} Since user cannot be authenticated by both session and API at once, the router should bind a property guiding us on what context we should work with
		*/
		function __construct(Orm $databaseAdapter, bool $checkToken = false) {

			$this->databaseAdapter = $databaseAdapter;

			$this->checkToken = $checkToken;

			$this->userSearched = 0;

			$this->sessionIdentifier = "tilwa_user_id";
		}

		// return database ID of signed in user
		public function getIdentifier ():int {

			$headers = getallheaders();

			$headerKey = "Authorization";

			if ($this->checkToken && array_key_exists($headerKey, $headers)) {

				$this->statelessLogin = true;

				$identifier = $headers[$headerKey]; // DESERIALIZE AND RETURN USER ID
				return $identifier;
			}
			return $_SESSION[$this->sessionIdentifier];
		}

		public function continueSession ():void {

			$userId = $this->getIdentifier();

			$user = null;

			if ($userId) $user = $this->databaseAdapter->findOne(User::class, $userId); // when not using default folder structure/ modular architecture, this would fail. The parent bootstrap should point to this class. your module overrides it at will, then binds that override to this class

			$this->setUser($user);

			$this->userSearched = 1;
		}

		public function getUser ():User {

			if ( $this->userSearched === 0)

				$this->continueSession();

			return $this->user; // clear this on logout
		}

		private function setUser (User $user) {

			$this->user = $user;
		}

		public function fromBrowser() {
			
			return !$this->statelessLogin;
		}

		public function initializeSession (int $userId):string {

			// get scope so we can know where to update
			// create token if necessary
			// update all
			// return token
		}
	}
?>