<?php

	namespace Modules;

	use Tilwa\App\Bootstrap;

	use AppRoutes\MainRoutes;

	use Tilwa\Routing\RouteManager;
	
	class Main extends Bootstrap {

		public function provideSelf ():self {

			$this->whenTypeAny()->needsAny(Bootstrap::class)

			->give($this);

			return $this;
		}

		public function getRootPath ():string {

			return dirname(__DIR__, 1) . DIRECTORY_SEPARATOR; // up one folder;
		}

		public function browserEntryRoute ():string {

			return MainRoutes::class;
		}

		public function apiStack ():array { // remove this after testing it

			return [
				"v1" => $this->getClass(RouteManager::class)->mirrorBrowserRoutes()
			];
		}
	}
?>