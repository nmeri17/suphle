<?php
	namespace Tilwa\Auth\Repositories;

	use Tilwa\Contracts\Auth\LoginActions;

	abstract class BaseAuthRepo implements LoginActions {

		protected $comparer;

		public function compareCredentials ():bool {

			return $this->comparer->compare();
		}

		public function failedLogin () {

			return ["message" => "Incorrect credentials"];
		}

		public function successRules ():array {

			return [
				"email" => "required|email",

				"password" => "required|alphanumeric|min:5"
			];
		}
	}
?>