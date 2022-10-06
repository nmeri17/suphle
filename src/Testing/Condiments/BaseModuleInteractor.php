<?php
	namespace Suphle\Testing\Condiments;

	use Suphle\Contracts\IO\{Session, CacheManager};

	use Suphle\Contracts\Queues\Adapter as QueueAdapter;

	use Suphle\Hydration\Container;

	use Suphle\Events\ModuleLevelEvents;

	use Suphle\Modules\{ModuleHandlerIdentifier, ModuleWorkerAccessor};

	use Suphle\Request\RequestDetails;

	use Suphle\Flows\OuterFlowWrapper;

	use Suphle\Adapters\{Session\InMemorySession, Cache\InMemoryCache};

	use Suphle\Testing\Proxies\ExceptionBroadcasters;

	trait BaseModuleInteractor {

		use ExceptionBroadcasters;

		private $originalModuleState, $preliminaryState;

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

		protected function beforeAllMethods ():void {

			$this->originalModuleState = $this->modules;
		}

		/**
		 * Restore original modules' state
		*/
		protected function beforeEachMethod (int $methodIndex):void {

			$this->entrance->setModules($this->originalModuleState);

			$this->setRequestPath("/refresh-descriptors");

			$this->preliminaryState = $this->entrance->getScopedDescriptors(); // a version for fixtures to work with

			$beforeDuping = $this->getContainer();

			$beforeDuping->lynx = 30;
			var_dump(98, spl_object_hash($beforeDuping));
		}

		/**
		 * Restore preliminary state
		*/
		protected function beforeEachFixture (int $fixtureIndex):void {
// dedup shit ain't work. that's why the second guy is still looking at ex container and can see mtr
			$this->entrance->setModules($this->preliminaryState, $fixtureIndex > 0); // at each interval, restore method state
			$currentContainer = $this->getContainer();
var_dump(109,

spl_object_hash($currentContainer), // problem starts here. it doesn't match 98/below

spl_object_hash($this->preliminaryState[0]->getContainer()), // does 27 (will come below) match this

$this->preliminaryState[0]->getContainer()->lynx, // works

$currentContainer->lynx // if this doesn't revert to 30, dupping didn't work
);
			$this->setRequestPath("/refresh-descriptors"); // but don't alter it so other fixtures can still refer to it
			$duped = $this->getContainer();
var_dump(123, spl_object_hash($duped), $duped->lynx);
			$this->modules = $this->entrance->getScopedDescriptors();
		}

		protected function afterAllMethods ():void {

			$this->entrance->setModules($this->originalModuleState);

			$this->modules = $this->originalModuleState;var_dump("afterAllMethods", spl_object_hash($this->getContainer())); // should rhyme with beforeAll
		}
	}
?>