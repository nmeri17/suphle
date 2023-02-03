<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Coordinators;

	use Suphle\Services\{ServiceCoordinator, Decorators\ValidationRules};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services\SystemModelEditMock1;

	class SystemModelController extends ServiceCoordinator {

		public function __construct(protected readonly SystemModelEditMock1 $editService) {

			//
		}

		#[ValidationRules([])] // Empty since test doesn't require routing to this controller
		public function handlePutRequest () { // supposed to send modefulPayload into editService->initializeUpdateModels. But for the purpose of this test, we'll return a predefined value

			if ($this->editService->updateModels())

				return ["message" => "success"];

			return ["message" => "failed"];
		}

		public function putOtherServiceMethod () {

			$this->editService->unrelatedToUpdate();
		}
	}
?>