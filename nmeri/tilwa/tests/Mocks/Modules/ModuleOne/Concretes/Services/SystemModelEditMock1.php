<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Tilwa\Services\UpdatelessService;

	use Tilwa\Contracts\Services\Decorators\SystemModelEdit;

	abstract class SystemModelEditMock1 extends UpdatelessService implements SystemModelEdit {

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

		public function rethrowAs ():array {

			return [];
		}
	}
?>