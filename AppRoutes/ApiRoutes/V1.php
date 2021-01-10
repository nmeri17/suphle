<?php

	namespace AppRoutes\ApiRoutes;

	use Tilwa\Routing\{Route, RouteCollection};

	class V1 extends RouteCollection {
		
		public function _index() {
			
			return $this->_mirrorBrowserRoutes();
		}
	}
?>