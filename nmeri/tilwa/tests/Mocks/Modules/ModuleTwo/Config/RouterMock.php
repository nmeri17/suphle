<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Router;

	class RouterMock extends Router {

		private $activeEntryRoute;

		public function __construct (string $entryRoute) {

			$this->activeEntryRoute = $entryRoute;
		}

		public function browserEntryRoute ():string {

			return $this->activeEntryRoute;
		}

		public function getModelRequestParameter():string {

			return "id";
		}
	}
?>