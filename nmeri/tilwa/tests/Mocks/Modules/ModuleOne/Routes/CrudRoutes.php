<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	class CrudRoutes extends BrowserNoPrefix { // try with/without prefix, with/without middleware, with/without auth
		
		public function crudRoutes() {
			
			return $this->_crud()->save(); // alter before saving. Also needs a view path. there are a number of methods on this apart from the crud methods themselves
		}
	}
?>