<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Hydration\Container;

	trait BaseModuleInteractor {

		/**
		 * Client test type must set [$this->modules]
		*/
		protected function massProvide (array $provisions):void {

			foreach ($this->modules as $descriptor) {

				$container = $descriptor->getContainer();

				foreach ($provisions as $parentType => $concrete)

					$container->refreshClass($parentType);

				$container->whenTypeAny()->needsAny($provisions);
			}
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}
	}
?>