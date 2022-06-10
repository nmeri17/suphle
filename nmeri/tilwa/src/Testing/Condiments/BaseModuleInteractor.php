<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\IO\{Session, CacheManager};

	use Tilwa\Hydration\Container;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\IO\{Session\InMemorySession, Cache\InMemoryCache};

	trait BaseModuleInteractor {

		protected $modules, // making this accessible for traits down the line that will need identical instances of the modules this base type is working with

		$entrance;

		protected function massProvide (array $provisions):void {

			foreach ($this->modules as $descriptor) {

				$container = $descriptor->getContainer();

				foreach ($provisions as $parentType => $concrete) {

					$container->refreshClass($parentType);
				}

				$container->whenTypeAny()->needsAny($provisions);
			}
		}

		/**
		 * Organic behavior is for only module matching incoming request to be prepared. But in tests, we may want to examine modular functionality without routing. In such case, boot everything
		 * 
		 * @param {skipFirst} Since base type is likely to have booted this automatically
		*/
		protected function prepareAllModules (bool $skipFirst = true):void {

			foreach ($this->modules as $index => $descriptor) {

				if ($index == 0 && $skipFirst) continue;

				$descriptor->prepareToRun();
			}
		}

		protected function getEventParent ():?ModuleLevelEvents {

			return null;
		}

		protected function firstModuleContainer ():Container {

			return $this->entrance->firstContainer();
		}

		protected function bootMockEntrance (ModuleHandlerIdentifier $entrance):void {

			foreach ($this->modules as $descriptor)

				$this->mayMonitorContainer($descriptor->getContainer());

			$entrance->bootModules();
 
			$entrance->extractFromContainer();
		}

		protected function setRequestPath (string $requestPath):void {

			RequestDetails::fromModules( $this->modules, $requestPath);
		}

		protected function provideTestEquivalents ():void {

			$this->massProvide([

				CacheManager::class => new InMemoryCache,
				
				Session::class => new InMemorySession
			]);
		}
	}
?>