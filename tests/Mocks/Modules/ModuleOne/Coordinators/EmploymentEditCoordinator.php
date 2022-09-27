<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\ServiceCoordinator;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\EmploymentEditMock;

	class EmploymentEditCoordinator extends ServiceCoordinator {

		private $editService;

		public function __construct (EmploymentEditMock $editService) {

			$this->editService = $editService;
		}

		public function simpleResult () {

			return [];
		}

		public function getEditableResource () {

			return [

				"data" => $this->editService->getResource()
			];
		}
	}
?>