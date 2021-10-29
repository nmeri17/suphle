<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\Crud;

	use Tilwa\Routing\BaseCollection;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Controllers\BaseController;

	class BasicRoutes extends BaseCollection {

		public function _handlingClass ():string {

			return BaseController::class;
		}
		
		public function SAVE__ALLh () {
			
			return $this->_crud()->save(); // Also needs a view path
		}
		
		public function DISABLE () {
			
			return $this->_crud()->save(); // Also needs a view path
		}
		
		public function OVERRIDE () { // continue here
			
			return $this->_crud()->save(); // alter before saving. Also needs a view path
		}
	}
?>