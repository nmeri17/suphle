<?php
	namespace Tilwa\Auth\Storage;

	use Tilwa\Contracts\Auth\UserHydrator;

	use Tilwa\Contracts\Config\Auth as AuthContract;

	class SessionStorage extends BaseAuthStorage {

		private $identifierKey = "tilwa_user_id",

		$previousUserKey = "previous_user";

		public function __construct (UserHydrator $userHydrator, AuthContract $authConfig) {

			$this->userHydrator = $userHydrator;

			$this->authConfig = $authConfig;
		}

		public function startSession (string $value):string {

			return $_SESSION[$this->identifierKey] = $value;
		}

		public function resumeSession ():void {

			$this->identifier = $_SESSION[$this->identifierKey];
		}

		public function loginAs (string $value):string {

			$this->setPreviousUser();

			return parent::loginAs($value);
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