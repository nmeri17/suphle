<?php
	namespace Suphle\Security\CSRF;

	use Suphle\Contracts\IO\Session;

	class CsrfGenerator {

		final const TOKEN_FIELD = "_csrf_token";

		public function __construct(private readonly Session $sessionClient)
  {
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