<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Contracts\Auth\LoginActions;

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

				"password" => "required|alpha_num|min:5"
			];
		}
	}
?>