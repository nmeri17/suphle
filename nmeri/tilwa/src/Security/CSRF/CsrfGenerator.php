<?php
	namespace Tilwa\Security\CSRF;

	use Tilwa\Contracts\IO\Session;

	class CsrfGenerator {

		const TOKEN_FIELD = "_csrf_token";

		private $sessionClient;

		public function __construct (Session $sessionClient) {

			$this->sessionClient = $sessionClient;
		}

		public function newToken ():string {

			$token = bin2hex(random_bytes(35));

			$this->sessionClient->setValue(self::TOKEN_FIELD, $token);

			return $token;
		}

		public function isVerifiedToken (string $incomingToken):bool {

			$savedToken = $this->sessionClient->getValue(self::TOKEN_FIELD);

			return !empty($savedToken) && $savedToken == $incomingToken;
		}
	}
?>