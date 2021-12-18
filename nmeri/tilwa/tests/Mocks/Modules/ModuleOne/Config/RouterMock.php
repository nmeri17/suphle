<?php

	namespace Tilwa\Tests\Mocks\Modules\ModuleOne\Config;

	use Tilwa\Config\Router;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\{ApiRoutes\V1\LowerMirror, BrowserNoPrefix};

	class RouterMock extends Router {

		public function browserEntryRoute ():string {

			return BrowserNoPrefix::class;
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