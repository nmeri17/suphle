<?php

	namespace Tilwa\App;
	
	use Dotenv\Dotenv;

	class ModuleToRoute {
		
		public function findContext(array $modules):ModuleInitializer {

			$requestQuery = $_GET['tilwa_request'];
			
			foreach($modules as $module) {

				$routeMatcher = (new ModuleInitializer($module, $module->getContainer(), $requestQuery))->assignRoute();
				
				if ($routeMatcher->foundRoute)

					return $routeMatcher;
			}
		}
	}
?>