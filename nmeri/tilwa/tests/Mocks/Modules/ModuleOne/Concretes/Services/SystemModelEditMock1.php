<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Services\{UpdatelessService, Structures\BaseErrorCatcherService};

	use Tilwa\Contracts\Services\Decorators\SystemModelEdit;

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