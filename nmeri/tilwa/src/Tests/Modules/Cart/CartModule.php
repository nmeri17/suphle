<?php

	namespace Tilwa\Modules\Auth;

	use Tilwa\App\{ModuleDescriptor, Container};

	use Routes\{BrowserRoutes, ApiRoutes\V1};
	
	// dismantle this into their respective configs
	class CartModule extends ModuleDescriptor {

		public function getRootPath ():string {

			return dirname(__DIR__, 1) . DIRECTORY_SEPARATOR; // up one folder;
		}

		public function browserEntryRoute ():string {

			return BrowserRoutes::class;
		}

		public function apiStack ():array { // remove this after testing it

			return [
				"v1" => V1::class
			];
		}
	}
?>