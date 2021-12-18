<?php
	namespace Tilwa\Auth\Repositories;

	use Tilwa\Contracts\Auth\LoginActions;

	use Tilwa\Auth\Validators\EmailPasswordValidator;

	abstract class BaseAuthRepo implements LoginActions {

		protected $comparer;

		public function compareCredentials ():bool {

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