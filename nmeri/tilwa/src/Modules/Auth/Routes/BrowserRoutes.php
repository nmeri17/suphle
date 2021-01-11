<?php

	namespace Tilwa\Modules\Auth\Routes;

	use Tilwa\Routing\RouteCollection;

	use Tilwa\Modules\Auth\Controllers\HandleAuth;

	use Tilwa\Http\Response\Format\{Markup,Json};

	class BrowserRoutes extends RouteCollection {
		
		public function _prefixCurrent() {
			
			return "auth";
		}

		public function _handlingClass ():string {

			return HandleAuth::class;
		}
		
		public function SHOW__LOGINh() {
			
			return $this->_get(new Markup("showLogin", "login-form"));
		}
		
		public function SUBMIT__LOGINh() {
			
			return $this->_post(new Json("handleLogin"));
		}
		
		public function SHOW__REGISTERh() {
			
			return $this->_get();
		}
	}
?>