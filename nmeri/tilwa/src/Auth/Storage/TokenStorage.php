<?php
	namespace Tilwa\Auth\Storage;

	use Tilwa\Contracts\{Config\AuthContract, IO\EnvAccessor};

	use Tilwa\Request\PayloadStorage;

	use Firebase\JWT\JWT;

	use Throwable;

	class TokenStorage extends BaseAuthStorage {

		private $payloadStorage, $envAccessor,

		$identifierKey = "user_id";

		public function __construct ( EnvAccessor $envAccessor, PayloadStorage $payloadStorage) {

			$this->envAccessor = $envAccessor;

			$this->payloadStorage = $payloadStorage;
		}

		public function resumeSession ():void {

			$payloadStorage = $this->payloadStorage;

			$headerKey = "Authorization";

			if (!$payloadStorage->hasHeader($headerKey)) return;

			$incomingToken = explode(" ", $payloadStorage->getHeader($headerKey) )[1]; // the bearer part

			try {
				$decoded = JWT::decode(

					$incomingToken,

					$this->envAccessor->getField("APP_SECRET_KEY"),

					["HS256"]
				);

				$this->identifier = $decoded["data"][$this->identifierKey];
			}
			catch(Throwable $e) { // why are we unable to decode token?

				var_dump($e->getMessage()); die();
			}
		}

		public function startSession(string $value):string {
			
			$issuedAt = time();

			$envAccessor = $this->envAccessor;

			$token = [
				"iss" => $envAccessor->getField("SITE_HOST"),
				// "aud" => $audience, // $audience
				"iat" => $issuedAt,

				"nbf" => $issuedAt + 10, // in seconds

				"exp" => $issuedAt + $envAccessor->getField("JWT_TTL"),

				"data" => [$this->identifierKey => $value]
			];

			return JWT::encode($token, $envAccessor->getField("APP_SECRET_KEY"));
		}
	}
?>