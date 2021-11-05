<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFive\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleFive\Routes\SecureBrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return SecureBrowserCollection::class;
		}
	}
?>