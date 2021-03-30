<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	class ModuleToRoute {
		
		public function findContext(array $modules):ModuleInitializer {

			$requestQuery = $_GET['tilwa_request'];
			
			foreach($modules as $module) {

				$routeMatcher = $this->getRouteMatcher($module, $requestQuery);
				
				if ($routeMatcher->foundRoute)

					return $routeMatcher;
			}
		}

		private function getHttpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}

		private function getRouteMatcher(ParentModule $module, string $requestQuery) {

			$container = $module->getContainer();

			$router = new RouteManager($module, $container, $requestQuery, $this->getHttpMethod());

			$module->entityBindings($router);

			return (new ModuleInitializer($module, $container, $router))
			->assignRoute();
		}
	}
?>