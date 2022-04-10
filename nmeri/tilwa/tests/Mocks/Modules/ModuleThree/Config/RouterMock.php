<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleThree\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleThree\Routes\BrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return BrowserCollection::class;
		}
	}
?>