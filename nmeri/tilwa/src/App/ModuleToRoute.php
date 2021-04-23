<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	use Tilwa\Http\Response\ResponseManager;

	class ModuleToRoute {
		
		public function findContext(array $modules, string $requestQuery):ModuleInitializer {
			
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

			$container->setServiceProviders($module->getServiceProviders());

			$responseManager = $container->getClass(ResponseManager::class);

			return (new ModuleInitializer($module, $responseManager, $router))
			->assignRoute();
		}
	}
?>