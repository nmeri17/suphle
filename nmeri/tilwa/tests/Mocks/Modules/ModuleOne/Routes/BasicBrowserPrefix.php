<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Routes;

	class BasicBrowserPrefix extends BaseBrowserRoutes {
		
		public function _prefixCurrent() {
			
			return "my-prefix";
		}
	}
?>