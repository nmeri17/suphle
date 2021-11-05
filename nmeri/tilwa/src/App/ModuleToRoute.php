<?php

	namespace Tilwa\App;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute {

		private $activeDescriptor;
		
		public function findContext(array $descriptors):ModuleInitializer {
			
			foreach($descriptors as $descriptor) {

				$routeMatcher = (new ModuleInitializer($descriptor))

				->initialize()->assignRoute();
				
				if ($routeMatcher->didFindRoute()) {

					$this->activeDescriptor = $descriptor;

					return $routeMatcher;
				}
			}
		}
		
		public function getActiveModule ():ModuleDescriptor {

			return $this->activeDescriptor;
		}
	}
?>