<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\CanaryCollections;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\CanaryController;

	use Tilwa\Response\Format\Json;

	class CollectionRequestHasFoo extends BaseCollection {

		public function _handlingClass ():string {

			return CanaryController::class;
		}

		public function SAME__URLh () {

			$this->_get(new Json("fooHandler"));
		}
	}
?>