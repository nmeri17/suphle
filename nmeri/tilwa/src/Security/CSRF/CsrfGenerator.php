<?php
	namespace Tilwa\Security\CSRF;

	class CsrfGenerator {

		const TOKEN_FIELD = "_csrf_token";

		public function newToken ():string {

			$token = bin2hex(random_bytes(35));

			$_SESSION[self::TOKEN_FIELD] = $token;

			return $token;
		}

		public function isVerifiedToken (string $incomingToken):bool {

			return $_SESSION[self::TOKEN_FIELD] == $incomingToken;
		}
	}
?>