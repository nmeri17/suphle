<?php
	namespace Tilwa\Modules;

	/**
	 * Manager/wrapper around [ModuleInitializer]
	*/
	class ModuleToRoute {

		private $activeDescriptor;
		
		public function findContext (array $descriptors):?ModuleInitializer {
			
			foreach ($descriptors as $descriptor) {

				$context = $descriptor->getContainer()->whenTypeAny()->needsAny([

					DescriptorInterface::class => $descriptor
				])
				->getClass(ModuleInitializer::class);

				$routeMatcher = $context->initialize()->assignRoute();
				
				if ($routeMatcher->didFindRoute()) {

					$this->activeDescriptor = $descriptor;

					return $routeMatcher;
				}
			}

			return null;
		}
		
		public function getActiveModule ():?ModuleDescriptor {

			return $this->activeDescriptor;
		}
	}
?>