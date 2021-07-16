<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\BrowserNoPrefix;

	class RouterMock extends Router {

		private $activeEntryRoute;

		public function __construct (string $entryRoute) {

			$this->activeEntryRoute = !empty($entryRoute) ? $entryRoute: BrowserNoPrefix::class;
		}

		public function browserEntryRoute ():string {

			return $this->activeEntryRoute;
		}

		public function getModelRequestParameter():string {

			return "id";
		}
	}
?>