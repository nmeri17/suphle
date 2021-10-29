<?php
	namespace Tilwa\Tests\Mocks\Modules\ModuleFour\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleFour\Routes\AuthenticatedRoutes;

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return AuthenticatedRoutes::class;
		}
	}
?>