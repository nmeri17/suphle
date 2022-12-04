<?php
	namespace Suphle\Auth\Storage;

	use Suphle\Contracts\IO\Session;

	use Suphle\Contracts\Config\AuthContract;

	class SessionStorage extends BaseAuthStorage {

		private string $identifierKey = "tilwa_user_id";
  private string $previousUserKey = "previous_user";
  private $isImpersonating;

		public function __construct(private readonly Session $sessionClient)
  {
  }

		/**
		 * {@inheritdoc}
		*/
		public function startSession (string $value):string {

			if (!$this->isImpersonating) { // protection against session fixation

				$this->logout();

				$this->sessionClient->startNew();
			}

			$this->sessionClient->setValue($this->identifierKey, $value);

			return $this->getId(); // trigger resumption
		}

		/**
		 * {@inheritdoc}
		*/
		public function resumeSession ():void {

			$this->identifier = $this->sessionClient->getValue($this->identifierKey);
		}

		/**
		 * {@inheritdoc}
		*/
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