<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Testing\Condiments\{ModuleReplicator, GagsException};

	use Tilwa\Testing\Proxies\{ModuleHttpTest, Extensions\FrontDoor};

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	abstract class ModuleLevelTest extends TestVirginContainer {

		use ModuleReplicator, GagsException, ModuleHttpTest {

			GagsException::setUp as mufflerSetup;
		}

		protected $muffleExceptionBroadcast = true, $entrance;

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor($this->getModules());

			$entrance->bootModules();

			$entrance->extractFromContainer();

			if ($this->muffleExceptionBroadcast)

				$this->mufflerSetup();
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules():array;

		protected function getModuleFor (string $interface) {

			foreach ($this->getModules() as $descriptor)

				if ($interface == $descriptor->exportsImplements()) {

					$descriptor->warmModuleContainer();

					$descriptor->prepareToRun();

					return $descriptor->materialize();
				}
		}

		protected function massProvide (array $provisions):void {

			foreach ($this->getModules() as $descriptor)

				$descriptor->getContainer()->whenTypeAny()

				->needsAny($provisions);
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}
	}
?>