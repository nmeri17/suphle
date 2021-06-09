<?php

	namespace Tilwa\App;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute {
		
		public function findContext(array $descriptors):ModuleInitializer {
			
			foreach($descriptors as $descriptor) {

				$routeMatcher = (new ModuleInitializer($descriptor))

				->initialize()->assignRoute();
				
				if ($routeMatcher->didFindRoute())

					return $routeMatcher;
			}
		}
	}
?>