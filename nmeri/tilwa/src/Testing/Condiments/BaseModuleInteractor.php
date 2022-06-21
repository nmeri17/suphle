<?php
	namespace Tilwa\Testing\Condiments;

	use Tilwa\Contracts\IO\{Session, CacheManager};

	use Tilwa\Contracts\Queues\Adapter as QueueAdapter;

	use Tilwa\Hydration\Container;

	use Tilwa\Events\ModuleLevelEvents;

	use Tilwa\Modules\ModuleHandlerIdentifier;

	use Tilwa\Request\RequestDetails;

	use Tilwa\Flows\OuterFlowWrapper;

	use Tilwa\IO\{Session\InMemorySession, Cache\InMemoryCache};

	use Tilwa\Testing\Proxies\ExceptionBroadcasters;

	trait BaseModuleInteractor {

		use ExceptionBroadcasters;

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

			$this->massProvide(array_merge([

				CacheManager::class => new InMemoryCache,
				
				Session::class => new InMemorySession,

				QueueAdapter::class => $this->positiveDouble(QueueAdapter::class)
			], $this->getExceptionDoubles()));
		}
	}
?>