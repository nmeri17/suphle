<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Concretes\Services\EmploymentEditMock, Validators\EmploymentValidator};

	class EmploymentEditCoordinator extends ServiceCoordinator {

		public function __construct(private readonly EmploymentEditMock $editService)
  {
  }

		public function validatorCollection ():string {

			return EmploymentValidator::class;
		}

		public function simpleResult () {

			return [];
		}

		public function getEmploymentDetails () {

			return [

				"data" => $this->editService->getResource()
			];
		}

		public function updateEmploymentDetails ():iterable {

			return [

				"message" => $this->editService->updateResource()
			];
		}
	}
?>