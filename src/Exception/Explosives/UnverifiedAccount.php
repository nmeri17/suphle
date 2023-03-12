<?php
	namespace Suphle\Exception\Explosives;

	use Exception;

	class UnverifiedAccount extends Exception {

		protected $code = 400;

		public function __construct (string $verificationUrl) {

			$this->message = "User is not verified. Visit ". $verificationUrl . " to begin verification process";
		}
	}
?>