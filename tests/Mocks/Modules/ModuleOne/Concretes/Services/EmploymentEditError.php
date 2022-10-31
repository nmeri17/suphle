<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	class EmploymentEditError extends EmploymentEditMock {

		public function updateResource (): never {

			trigger_error("nonsensical", E_USER_ERROR);
		}

		public function failureState (string $method) {

			if ($method == "updateResource")

				return "boo!";
		}
	}
?>