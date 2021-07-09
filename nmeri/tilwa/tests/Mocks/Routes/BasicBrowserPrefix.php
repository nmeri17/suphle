<?php

	namespace Tilwa\Tests\Mocks\Routes;

	class BasicBrowserPrefix extends BaseBrowserRoutes {
		
		public function _prefixCurrent() {
			
			return "my-prefix";
		}
	}
?>