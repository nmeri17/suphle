<?php

	namespace Tilwa\Http\Request;

	use Tilwa\Contracts\{Orm, Authenticator};

	use Firebase\JWT\JWT;

	class NativeAuth implements Authenticator {

		private $user;

		private $userSearched;

		private $databaseAdapter;

		private $sessionIdentifier;

		private $userModel;

		private $isApiRoute;

		private $secretKey;

		/**
		* @param {isApiRoute} Since user cannot be authenticated by both session and API at once, the router should bind a property guiding us on what context we should work with
		*/
		function __construct(Orm $databaseAdapter, string $userModel, bool $isApiRoute) {

			$this->databaseAdapter = $databaseAdapter;

			$this->isApiRoute = $isApiRoute;

			$this->userSearched = 0;

			$this->sessionIdentifier = "tilwa_user_id";

			$this->userModel = $userModel;

			$this->secretKey = getenv("APP_SECRET_KEY");
		}

		// return database ID of signed in user
		public function getIdentifier ():int {

			if ($this->isApiRoute) {

				$headers = getallheaders();

				$headerKey = "Authorization";

				if (array_key_exists($headerKey, $headers)) {

					$incomingToken = explode(" ", $headers[$headerKey] )[1]; // the bearer part

					try {
						$decoded = JWT::decode($incomingToken, $this->secretKey, ["HS256"]);

						var_dump($decoded); die();

						return $decoded["data"]["user_id"];
					}
					catch(Exception $e) {
						var_dump($e->getMessage()); die();
					}
				}
			}
			return $_SESSION[$this->sessionIdentifier];
		}

		public function continueSession ():void {

			$userId = $this->getIdentifier();

			$user = null;

			if ($userId) {

				$user = $this->databaseAdapter->findOne($this->userModel, $userId);

				$this->initializeSession($user->id);
			}
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

			if ($this->isApiRoute)
				
				return $this->generateToken($userId);

			return $_SESSION[$this->sessionIdentifier] = "$userId";
		}

		public function terminateSession (string $identifier):void {

			if (!$this->isApiRoute)
				
				$_SESSION[$this->sessionIdentifier] = null;
		}

		private function generateToken(int $user_id):string {
			
			$issuer = getenv("SITE_HOST");

			$issuedAt = time();

			$notBefore = $issuedAt + 10; // in seconds
			
			$expire = $issuedAt + getenv("JWT_TTL");
			
			$token = [
				"iss" => $issuer,
				// "aud" => $audience, // $audience
				"iat" => $issuedAt,
				"nbf" => $notBefore,
				"exp" => $expire,
				"data" => compact( "user_id")
			];
			return JWT::encode($token, $this->secretKey);
		}
	}
?>