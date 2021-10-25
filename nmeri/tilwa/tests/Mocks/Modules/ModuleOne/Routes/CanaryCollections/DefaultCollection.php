<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\CanaryController;

	use Tilwa\Response\Format\Json;

	class DefaultCollection extends BaseCollection {

		public function _handlingClass ():string {

			return CanaryController::class; // in a real app, these canaries and their collections will point to different controllers
		}

		public function SAME__URLh () {

			$this->_get(new Json("defaultHandler"));
		}

		public function id () {

			$this->_get(new Json("defaultPlaceholder"));
		}
	}
?>