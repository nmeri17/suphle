<?php

	namespace Tilwa\Modules\Auth;

	use Tilwa\App\{ParentModule, Container};

	use Routes\{BrowserRoutes, ApiRoutes\V1};
	
	class Module extends ParentModule {

		function __construct(Container $container) {
			
			$this->container = $container;
		}

		public function entityBindings ():self {

			$this->container->whenTypeAny()->needsAny([ // this should be always be the first binding so it can supply the active module to every client requesting the base type

				ParentModule::class => $this
			]);
			return $this;
		}

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