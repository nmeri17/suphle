<?php
	namespace Suphle\Auth\Storage;

	use Suphle\Contracts\{Config\AuthContract, IO\EnvAccessor};

	use Suphle\Request\PayloadStorage;

	use Firebase\JWT\{JWT, Key};

	use Throwable;

	class TokenStorage extends BaseAuthStorage {

		const AUTHORIZATION_HEADER = "Authorization";

		private $payloadStorage, $envAccessor,

		$identifierKey = "user_id";

		public function __construct ( EnvAccessor $envAccessor, PayloadStorage $payloadStorage) {

			$this->envAccessor = $envAccessor;

			$this->payloadStorage = $payloadStorage;
		}

		public function resumeSession ():void {

			if (!$this->payloadStorage->hasHeader(self::AUTHORIZATION_HEADER))

				return;

			try {

				$incomingToken = explode(" ",

					$this->payloadStorage->getHeader(self::AUTHORIZATION_HEADER)
				)[1]; // the bearer part

				$decoded = JWT::decode(

					$incomingToken, new Key(

						$this->envAccessor->getField("APP_SECRET_KEY"),

						"HS256"
					)
				);
			}
			catch (Throwable $exception) {

				var_dump("Unable to decode token",

					$exception->getMessage(), get_class($exception)
				);

				return;
			}

			$this->identifier = $decoded->data->{$this->identifierKey};
		}

		public function startSession(string $value):string {
			
			$issuedAt = time();

			$envAccessor = $this->envAccessor;

			$tokenDetails = [
				"iss" => $envAccessor->getField("SITE_HOST"),
				// "aud" => $audience, // $audience
				"iat" => $issuedAt,

				//"nbf" => $issuedAt + 10, // in seconds

				"exp" => $issuedAt + $envAccessor->getField("JWT_TTL"),

				"data" => [$this->identifierKey => $value]
			];

			$outgoingToken = JWT::encode(
				$tokenDetails, $envAccessor->getField("APP_SECRET_KEY")
			);

			return $outgoingToken;
		}
	}
?>