<?php
	namespace Suphle\Auth\Storage;

	use Suphle\Contracts\IO\Session;

	use Suphle\Contracts\Config\AuthContract;

	class SessionStorage extends BaseAuthStorage {

		private $identifierKey = "tilwa_user_id",

		$previousUserKey = "previous_user",

		$isImpersonating, $sessionClient;

		public function __construct ( Session $sessionClient) {

			$this->sessionClient = $sessionClient;
		}

		public function startSession (string $value):string {

			if (!$this->isImpersonating) { // protection against session fixation

				$this->logout();

				$this->sessionClient->startNew();
			}

			$this->sessionClient->setValue($this->identifierKey, $value);

			return $this->getId(); // trigger resumption
		}

		public function resumeSession ():void {

			$this->identifier = $this->sessionClient->getValue($this->identifierKey);
		}

		public function imitate (string $value):string {

			$this->setPreviousUser();

			$this->isImpersonating = true;

			return parent::imitate($value);
		}

		protected function setPreviousUser ():void {

			if (!$this->hasActiveAdministrator())

				$this->sessionClient->setValue($this->previousUserKey, $this->identifier);
		}

		public function getPreviousUser ():?string {

			return $this->sessionClient->getValue($this->previousUserKey);
		}

		public function hasActiveAdministrator ():bool {

			return $this->sessionClient->hasKey($this->previousUserKey);
		}

		public function logout ():void {

			parent::logout();

			$this->sessionClient->reset();
		}
	}
?>