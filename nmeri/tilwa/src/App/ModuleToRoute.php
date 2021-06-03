<?php

	namespace Tilwa\App;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute {
		
		public function findContext(array $modules, string $requestPath):ModuleInitializer {
			
			foreach($modules as $module) {

				$routeMatcher = $this->getRouteMatcher($module, $requestPath);
				
				if ($routeMatcher->didFindRoute())

					return $routeMatcher;
			}
		}

		private function getHttpMethod ():string {

			return strtolower(

				$_POST["_method"] ?? $_SERVER['REQUEST_METHOD']
			);
		}

		private function getRouteMatcher(ModuleDescriptor $descriptor, string $requestPath):ModuleInitializer {

			return (new ModuleInitializer($descriptor, $requestPath, $this->getHttpMethod()))

			->initialize()->assignRoute();
		}
	}
?>