<?php

	namespace AppRoutes\ApiRoutes;

	use Tilwa\Routing\BaseCollection;

	class V1 extends BaseCollection {
		
		public function _index() {
			
			return $this->_mirrorBrowserRoutes();
		}
	}
?>