<?php
	namespace AllModules\_module_name\Config;

	use Suphle\Config\Router;

	use AllModules\_module_name\Routes\BrowserCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return BrowserCollection::class;
		}
	}
?>