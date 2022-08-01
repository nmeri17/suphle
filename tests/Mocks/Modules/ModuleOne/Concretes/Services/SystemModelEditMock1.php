<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Services\{UpdatelessService, Structures\BaseErrorCatcherService};

	use Suphle\Contracts\Services\Decorators\SystemModelEdit;

	class SystemModelEditMock1 extends UpdatelessService implements SystemModelEdit {

		use BaseErrorCatcherService;

		public function updateModels () {

			return true;
		}

		public function modelsToUpdate ():array {

			return [];
		}

		public function initializeUpdateModels ($baseModel):void {

			//
		}

		public function unrelatedToUpdate () {}
	}
?>