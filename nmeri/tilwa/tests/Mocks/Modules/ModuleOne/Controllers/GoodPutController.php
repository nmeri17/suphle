<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers;

	use Tilwa\Services\ServiceCoordinator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Validators\ValidatorOne;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services\{SystemModelEditMock1};

	class GoodPutController extends ServiceCoordinator {

		private $editService;

		public function __construct (SystemModelEditMock1 $editService) {

			$this->editService = $editService;
		}

		public function validatorCollection ():?string {

			return ValidatorOne::class;
		}

		public function handlePutRequest () { // supposed to send modefulPayload into editService->initializeUpdateModels. But for the purrpose, of this test, we'll return a predefined value

			if ($this->editService->updateModels())

				return ["message" => "success"];

			return ["message" => "failed"];
		}

		public function putOtherServiceMethod () {

			$this->editService->unrelatedToUpdate();
		}
	}
?>