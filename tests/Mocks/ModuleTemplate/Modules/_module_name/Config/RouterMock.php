<?php
	namespace Suphle\Tests\Mocks\Modules\_module_name\Config;

	use Suphle\Config\Router;

	use Suphle\Tests\Mocks\Modules\_module_name\Routes\BrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():?string {

			return BrowserCollection::class;
		}
	}
?>