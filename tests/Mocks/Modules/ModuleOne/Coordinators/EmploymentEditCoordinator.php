<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\EmploymentEditMock};

	class EmploymentEditCoordinator extends ServiceCoordinator {

		public function __construct(protected readonly EmploymentEditMock $editService) {

			//
		}

		public function simpleResult () {

			return [];
		}

		public function getEmploymentDetails () {

			return [

				"data" => $this->editService->getResource()
			];
		}

		#[ValidationRules([
			"id" => "required|numeric|exists:employment,id",

			"salary" => "numeric|min:20000"
		])]
		public function updateEmploymentDetails ():iterable {

			return [

				"message" => $this->editService->updateResource()
			];
		}
	}
?>