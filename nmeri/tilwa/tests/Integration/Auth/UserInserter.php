<?php
	namespace Tilwa\Tests\Integration\Auth;

	use Tilwa\Testing\{PopulatesDatabaseTest, DirectHttpTest};

	use Tilwa\Contracts\Auth\User;

	/**
	 * Helper class for adding a fresh user, then using his details for login
	*/
	class UserInserter {

		use PopulatesDatabaseTest, DirectHttpTest;

		private $correctPassword = "correct",

		$incorrectPassword = "incorrect",

		$loginPath = "api/v1/login";

		public function getInsertedUser (string $password):User {
			
			$user = $this->getBeforeInsertion(1, [ // inserting a new row rather than pulling a random one so we can access the "password" field during login request

				"password" => password_hash($password, PASSWORD_DEFAULT)
			]);

			$user->save();

			return $user;
		}

		public function sendCorrectRequest ():void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->setJsonParams($this->loginPath, [

				"email" => $user->email,

				"password" => $this->correctPassword
			]);
		}

		public function sendIncorrectRequest ():void {

			$user = $this->getInsertedUser($this->correctPassword);

			$this->setJsonParams($this->loginPath, [

				"email" => $user->email,

				"password" => $this->incorrectPassword
			]);
		}

		public function getLoginPath ():string {

			return $this->loginPath;
		}
	}
?>