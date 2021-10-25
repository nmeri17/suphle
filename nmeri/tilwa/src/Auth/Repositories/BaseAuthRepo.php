<?php
	namespace Tilwa\Auth\Repositories;

	use Tilwa\Contracts\Auth\LoginActions;

	use Tilwa\Auth\Validators\EmailPasswordValidator;

	abstract class BaseAuthRepo implements LoginActions {

		protected $comparer, $authStorage;

		public function compareCredentials ():bool {

			$this->comparer->setAuthMechanism($this->authStorage);

			return $this->comparer->compare();
		}

		public function failedLogin () {

			return ["message" => "Incorrect credentials"];
		}

		public function validatorCollection ():string {

			return EmailPasswordValidator::class;
		}
	}
?>