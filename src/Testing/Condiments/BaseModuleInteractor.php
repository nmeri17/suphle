<?php
	namespace Suphle\Testing\Condiments;

	use Suphle\Contracts\IO\{Session, CacheManager};

	use Suphle\Contracts\Queues\Adapter as QueueAdapter;

	use Suphle\Hydration\Container;

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Modules\ModuleHandlerIdentifier

	use Suphle\Server\ModuleWorkerAccessor;

	use Suphle\Request\RequestDetails;

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Adapters\{Session\InMemorySession, Cache\InMemoryCache};

	use Suphle\Testing\Proxies\ExceptionBroadcasters;

	trait BaseModuleInteractor {

		use ExceptionBroadcasters;

		protected array $modules; // making this accessible for traits down the line that will need identical instances of the modules this base type is working with

		protected ModuleHandlerIdentifier $entrance;

		protected function massProvide (array $provisions):void {

			foreach ($this->modules as $descriptor) {

				$container = $descriptor->getContainer();

				$container->refreshMany(array_keys($provisions));

				$container->whenTypeAny()->needsAny($provisions);
			}
		}

		protected function firstModuleContainer ():Container {

			return $this->entrance->firstContainer();
		}

		protected function bootMockEntrance (ModuleHandlerIdentifier $entrance):void {

			$this->monitorModuleContainers();

			(new ModuleWorkerAccessor($entrance, true))

			->buildIdentifier();
		}

		protected function monitorModuleContainers ():void {

			foreach ($this->modules as $descriptor)

				$this->mayMonitorContainer($descriptor->getContainer());
		}

		protected function setRequestPath (string $requestPath):void {

			$this->entrance->setRequestPath($requestPath);
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