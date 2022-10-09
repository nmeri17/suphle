<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Validators;

	class EmploymentValidator {

		public function updateEmploymentDetails ():array {

			return [
				"id" => "required|numeric|exists:employment,id",

				"salary" => "numeric|min:20000"
			];
		}
	}
?>