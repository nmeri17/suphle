<?php

	namespace Tilwa\Tests\Modules\Cart\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Tests\Modules\Controllers\HandleCart;

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