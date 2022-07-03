<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\MultiUserEditMock;

	class MultiEditController extends ServiceCoordinator {

		private $editService;

		public function __construct (MultiUserEditMock $editService) {

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