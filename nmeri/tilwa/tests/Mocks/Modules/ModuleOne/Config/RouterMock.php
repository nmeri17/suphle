<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\V1\LowerMirror;

	class RouterMock extends Router {

		private $activeEntryRoute;

		public function __construct (string $entryRoute) {

			$this->activeEntryRoute = $entryRoute;
		}

		public function browserEntryRoute ():string {

			return $this->activeEntryRoute;
		}

		public function apiStack ():array {

			return [

				"v1" => LowerMirror::class
			];
		}

		public function mirrorsCollections ():bool {

			return true;
		}
	}
?>