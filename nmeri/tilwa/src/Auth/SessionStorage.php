<?php

	namespace Tilwa\Auth;

	use Tilwa\Contracts\{Auth\UserHydrator, Config\Auth as AuthContract};

	class SessionStorage extends BaseAuthStorage {

		private $identifierKey = "tilwa_user_id";

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

		public function logout ():void {

			parent::logout();

			$_SESSION = [];

			session_destroy();
		}
	}
?>