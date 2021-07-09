<?php
	namespace Tilwa\Tests\Mocks\Routes;

	class CrudRoutes extends BrowserNoPrefix { // try with/without prefix, with/without middleware, with/without auth
		
		public function crudRoutes() {
			
			return $this->_crud()->save();
		}
	}
?>