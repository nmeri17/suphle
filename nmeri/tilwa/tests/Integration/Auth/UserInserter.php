<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Contracts\Auth\UserContract;

	/**
	 * Helper trait for adding a fresh user, then using his details for login
	*/
	trait UserInserter {

		use DirectHttpTest;

		private $correctPassword = "correct",

		$incorrectPassword = "incorrect";

		public function getInsertedUser (string $password):UserContract {

			return $this->replicator->modifyInsertion(1, [ // inserting a new row rather than pulling a random one so we can access the "password" field during login request

				"password" => password_hash($password, PASSWORD_DEFAULT)
			])->first();
		}

		public function sendCorrectRequest (string $loginPath):void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->setJsonParams($loginPath, [

				"email" => $user->email,

				"password" => $this->correctPassword
			]);
		}

		public function sendIncorrectRequest (string $loginPath):void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->setJsonParams($loginPath, [

				"email" => $user->email,

				"password" => $this->incorrectPassword
			]);
		}

		public function getLoginPath ():string {

			return $this->loginPath;
		}
	}
?>