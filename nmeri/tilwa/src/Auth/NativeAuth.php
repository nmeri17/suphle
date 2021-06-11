<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\{Orm, Authenticator, Config\{Authenticator as AuthConfig, Router as RouterConfig}};

	use Firebase\JWT\JWT;

	class NativeAuth implements Authenticator {

		private $user, $databaseAdapter, $config,

		$routerConfig;

		private $userSearched = 0;

		private $secretKey = getenv("APP_SECRET_KEY");

		function __construct(Orm $databaseAdapter, AuthConfig $authConfig, RouterConfig $routerConfig) {

			$this->databaseAdapter = $databaseAdapter;

			$this->routerConfig = $routerConfig;

			$this->authConfig = $authConfig;
		}

		// return database ID of signed in user
		public function getIdentifier ():int {

			if ($this->routerConfig->isApiRoute()) {

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

		public function hydrateUser ():void {

			$userId = $this->getIdentifier();

			$user = null;

			if ($userId) {

				$user = $this->databaseAdapter->findOne($this->authConfig->getUserModel(), $userId);

				$this->setIdentifier($user->id);
			}
			$this->setUser($user);

			$this->userSearched = 1;
		}

		// correct this guy's usages
		public function getUser ():User {

			if ( $this->userSearched === 0)

				$this->hydrateUser();

			return $this->user; // clear this on logout
		}

		private function setUser (User $user) {

			$this->user = $user;
		}

		public function setIdentifier (int $userId):void {

			if ($this->isApiRoute)
				
				return $this->generateToken($userId);
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