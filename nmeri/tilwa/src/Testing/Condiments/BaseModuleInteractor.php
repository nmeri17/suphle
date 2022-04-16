<?php
	namespace Tilwa\Testing\Condiments;

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

		/**
		 * Organic behavior is for only module matching incoming request to be prepared. But in tests, we may want to examin mdular functionality without routing. In such case, boot everything
		 * 
		 * @param {skipFirst} Since base type is likely to have booted this automatically
		*/
		protected function prepareAllModules (bool $skipFirst = true):void {

			foreach ($this->modules as $index => $descriptor) {

				if ($index == 0 && $skipFirst) continue;

				$descriptor->prepareToRun();
			}
		}
	}
?>