<?php

	namespace Tilwa\Auth;

	use Firebase\JWT\JWT;

	use Tilwa\Contracts\{UserHydrator, Config\Auth as AuthContract};

	use Throwable;

	use Tilwa\Request\PayloadStorage;

	class TokenStorage extends BaseAuthStorage {

		private $payloadStorage, $identifierKey = "user_id";

		public function __construct (UserHydrator $userHydrator, AuthContract $authConfig, PayloadStorage $payloadStorage) {

			$this->userHydrator = $userHydrator;

			$this->authConfig = $authConfig;

			$this->payloadStorage = $payloadStorage;
		}

		public function resumeSession ():void {

			$payloadStorage = $this->payloadStorage;

			$headerKey = "Authorization";

			if (!$payloadStorage->hasHeader($headerKey))

				return null;

			$incomingToken = explode(" ", $payloadStorage->getHeader($headerKey) )[1]; // the bearer part

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