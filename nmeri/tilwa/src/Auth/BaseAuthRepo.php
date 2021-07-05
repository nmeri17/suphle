<?php
	namespace Tilwa\Auth;

	use Tilwa\Contracts\LoginActions;

	abstract class BaseAuthRepo implements LoginActions {

		protected $comparer, $authStorage;

		public function compareCredentials ():bool {

			$this->comparer->setAuthMechanism($this->authStorage);

			return $this->comparer->compare();
		}

		public function failedLogin () {

			return ["message" => "Incorrect credentials"];
		}
	}
?>