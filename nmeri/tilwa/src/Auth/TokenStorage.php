<?php

	namespace Tilwa\Auth;

	use Firebase\JWT\JWT;

	use Tilwa\Contracts\{Orm, Config\Auth as AuthContract};

	use Throwable;

	class TokenStorage extends BaseAuthStorage {

		private $identifierKey = "user_id";

		public function __construct (Orm $databaseAdapter, AuthContract $authConfig) {

			$this->databaseAdapter = $databaseAdapter;

			$this->authConfig = $authConfig;
		}

		public function resumeSession ():void {

			$headers = getallheaders();

			$headerKey = "Authorization";

			if (!array_key_exists($headerKey, $headers))

				return null;

			$incomingToken = explode(" ", $headers[$headerKey] )[1]; // the bearer part

			try {
				$decoded = JWT::decode($incomingToken, $this->config->getTokenSecretKey(), ["HS256"]);

				$this->identifier = $decoded["data"][$this->identifierKey];
			}
			catch(Throwable $e) { // why are we unable to decode token?

				var_dump($e->getMessage()); die();
			}
		}

		public function startSession(string $value):string {
			
			$issuedAt = time();

			$config = $this->config;
			
			$token = [
				"iss" => $config->getTokenIssuer(),
				// "aud" => $audience, // $audience
				"iat" => $issuedAt,

				"nbf" => $issuedAt + 10, // in seconds

				"exp" => $issuedAt + $config->getTokenTtl(),

				"data" => [$this->identifierKey => $value]
			];

			return JWT::encode($token, $config->getTokenSecretKey());
		}
	}
?>