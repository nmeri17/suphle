<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleTwo\Routes\BrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return BrowserCollection::class;
		}
	}
?>