<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFour\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleFour\Routes\AuthenticateCrudCollection;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return AuthenticateCrudCollection::class;
		}
	}
?>