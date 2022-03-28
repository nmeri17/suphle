<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Hydration\Container;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Testing\Condiments\{ModuleReplicator, GagsException, MockFacilitator};

	use Tilwa\Testing\Proxies\{ModuleHttpTest, Extensions\FrontDoor};

	abstract class ModuleLevelTest extends TestVirginContainer {

		use ModuleReplicator, GagsException, ModuleHttpTest, MockFacilitator {

			GagsException::setUp as mufflerSetup;
		}

		private $modules;

		protected $muffleExceptionBroadcast = true, $entrance;

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor(
				
				$this->modules = $this->getModules() // storing in an instance variable instead of reading directly from method so mutative methods can iterate and modify
			);

			$this->massProvideSession(); // this needs to happen before module boots, so hydration of a session-using object doesn't override our provision

			$entrance->bootModules();

			$entrance->extractFromContainer();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();

			$cacheManager = \Tilwa\Contracts\CacheManager::class;

			$this->massProvide([

				$cacheManager => $this->negativeDouble($cacheManager, [])
			]);
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules ():array;

		/**
		 * Doesn't return the descriptor but rather the concrete associated with inteface exported by given module
		*/
		protected function getModuleFor (string $interface) {

			foreach ($this->getModules() as $descriptor)

				if ($interface == $descriptor->exportsImplements()) {

					$descriptor->warmModuleContainer();

					$descriptor->prepareToRun();

					return $descriptor->materialize();
				}
		}

		protected function massProvide (array $provisions):void {

			foreach ($this->modules as $descriptor)

				$descriptor->getContainer()->whenTypeAny()

				->needsAny($provisions);
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}

		protected function dataProvider (array $callables, callable $testBody):void {

			foreach ($callables as $provider)

				foreach ($provider() as $dataFixture)

					$testBody(...$dataFixture);
		}
	}
?>