<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\{Orm, Authenticator as AuthInterface};

	class Authenticator implements AuthInterface {

		private $user;

		private $userSearched;

		private $databaseAdapter;

		private $sessionIdentifier;

		private $userModel;

		private $isApiRoute;

		/**
		* @param {isApiRoute} Since user cannot be authenticated by both session and API at once, the router should bind a property guiding us on what context we should work with
		*/
		function __construct(Orm $databaseAdapter, string $userModel, bool $isApiRoute) {

			$this->databaseAdapter = $databaseAdapter;

			$this->isApiRoute = $isApiRoute;

			$this->userSearched = 0;

			$this->sessionIdentifier = "tilwa_user_id";

			$this->userModel = $userModel;
		}

		// return database ID of signed in user
		public function getIdentifier ():int {

			if ($this->isApiRoute) {

				$headers = getallheaders();

				$headerKey = "Authorization";

				if (array_key_exists($headerKey, $headers)) {

					$identifier = $headers[$headerKey]; // DESERIALIZE AND RETURN USER ID
					return $identifier;
				}
			}
			else return $_SESSION[$this->sessionIdentifier];
		}

		public function continueSession ():void {

			$userId = $this->getIdentifier();

			$user = null;

			if ($userId) $user = $this->databaseAdapter->findOne($this->userModel, $userId);

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

		public function initializeSession (int $userId):string {

			// get scope so we can know where to update
			// create token if necessary
			// update all
			// return token
		}
	}
?>