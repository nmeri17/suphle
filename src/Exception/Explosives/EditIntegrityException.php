<?php
	namespace Suphle\Exception\Explosives;

	use Exception;

	class EditIntegrityException extends Exception {

		final const NO_AUTHORIZER = 1, KEY_MISMATCH = 2, MISSING_KEY = 3;

		public function __construct (private readonly int $integrityType) {

			$this->setMessage();
		}

		public function setMessage ():void {

			$this->message = [

				self::NO_AUTHORIZER => "No path authorizer found",

				self::KEY_MISMATCH => "Mismatching update integrity key",

				self::MISSING_KEY => "No update integrity key found"
			][$this->integrityType];
		}

		public function getIntegrityType ():int {

			return $this->integrityType;
		}
	}
?>