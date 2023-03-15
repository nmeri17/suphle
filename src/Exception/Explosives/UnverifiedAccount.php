<?php
	namespace Suphle\Exception\Explosives;

	use Exception;

	class UnverifiedAccount extends Exception {

		public function __construct (public readonly string $verificationUrl) { // not handled the mirroring bits

			//
		}
	}
?>