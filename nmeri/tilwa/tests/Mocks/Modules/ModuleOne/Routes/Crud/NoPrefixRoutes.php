<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	class NoPrefixRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}
		
		public function SAVE__ALLh () {
			
			return $this->_crud()->save(); // Also needs a view path
		}
	}
?>