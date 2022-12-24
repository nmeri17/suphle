<?php
	namespace Suphle\Auth\Repositories;

	use Suphle\Contracts\Auth\{LoginActions, ColumnPayloadComparer};

	abstract class BaseAuthRepo implements LoginActions {

		/**
		 * Expects sub-classes to inject an ColumnPayloadComparer $comparer. Can't set the property here to avoid visibility headaches
		*/
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