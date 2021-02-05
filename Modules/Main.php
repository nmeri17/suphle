<?php

	namespace Modules;

	use Tilwa\App\{ParentModule, Container};

	use AppRoutes\{MainRoutes, ApiRoutes\V1};
	
	class Main extends ParentModule {

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		public function provideSelf ():self {

			$this->container->whenTypeAny()->needsAny([

				ParentModule::class => $this
			]);
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
				"v1" => V1::class
			];
		}
	}
?>