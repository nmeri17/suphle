<?php

	namespace Tilwa\App;

	class ModuleToRoute {
		
		function findContext(array $modules):FrontController {
			
			$context = null;

			foreach($modules as $module) if (is_null($context)) {

				$routeMatcher = (new FrontController($module))->assignRoute();
				
				if ($routeMatcher->foundRoute) $context = $routeMatcher;
			}
			
			return $context;
		}
	}
?>