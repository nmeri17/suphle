<?php

	namespace Tilwa\Modules\Auth\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Modules\Auth\Controllers\HandleAuth;

	use Tilwa\Http\Response\Format\{Markup,Json};

	class BrowserRoutes extends RouteCollection {
		
		public function _prefixCurrent() {
			
			return "cart";
		}

		public function _handlingClass ():string {

			return HandleCart::class;
		}
		
		public function crudRoutes() {
			
			return $this->_crud()->save();
		}
	}
?>