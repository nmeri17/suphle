<?php
	namespace Suphle\Tests\Mocks\Modules\ModuleTwo\Config;

	use Suphle\Config\Router;

	use Suphle\Tests\Mocks\Modules\ModuleTwo\Routes\BrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return BrowserCollection::class;
		}
	}
?>