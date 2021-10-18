<?php
	namespace Tilwa\Auth;

	use Tilwa\Contracts\Auth\LoginValidator;

	class EmailPasswordValidator implements LoginValidator {

		public function successRules ():array {

			return [
				"email" => "required|email",

				"password" => "required|alphanumeric|min:5"
			];
		}
	}
?>