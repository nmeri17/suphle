<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleOne\Concretes\Services;

	use Suphle\Services\{UpdatefulService, Structures\BaseErrorCatcherService};

	use Suphle\Contracts\Services\Decorators\{SystemModelEdit, VariableDependencies};

	class SystemModelEditMock1 extends UpdatefulService implements SystemModelEdit, VariableDependencies {

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