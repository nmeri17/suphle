<?php
	namespace Tilwa\Auth\Storage;

	use Tilwa\Contracts\Auth\UserHydrator;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	class SessionStorage extends BaseAuthStorage {

		private $identifierKey = "tilwa_user_id",

		$previousUserKey = "previous_user",

		$isImpersonating;

		public function __construct (UserHydrator $userHydrator, AuthContract $authConfig) {

			$this->userHydrator = $userHydrator;

			$this->authConfig = $authConfig;
		}

		public function startSession (string $value):string {

			if (!$this->isImpersonating) { // protection against session fixation

				$this->logout();

				session_start();
			}

			return $_SESSION[$this->identifierKey] = $value;
		}

		public function resumeSession ():void {

			$this->identifier = $_SESSION[$this->identifierKey];
		}

		public function impersonate (string $value):string {

			$this->setPreviousUser();

			$this->isImpersonating = true;

			return parent::impersonate($value);
		}

		protected function setPreviousUser ():void {

			$_SESSION[$this->previousUserKey] = $this->identifier;
		}

		public function getPreviousUser ():string {

			return $_SESSION[$this->previousUserKey];
		}

		public function hasPreviousUser ():bool {

			return array_key_exists($this->previousUserKey, $_SESSION);
		}

		public function logout ():void {

			parent::logout();

			$_SESSION = [];

			session_destroy();
		}
	}
?>