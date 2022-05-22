<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

	use Tilwa\Testing\Proxies\{ModuleHttpTest, GagsException, Extensions\FrontDoor};

	abstract class ModuleLevelTest extends TestVirginContainer {

		use ModuleReplicator, GagsException, ModuleHttpTest, BaseModuleInteractor {

			GagsException::setUp as mufflerSetup;
		}

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor(
				
				$this->modules = $this->getModules(), // storing in an instance variable instead of reading directly from method so mutative methods can iterate and modify

				$this->getEventParent()
			);

			$this->provideTestEquivalents();

			$this->bootMockEntrance($entrance);

			$this->mufflerSetup();
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules ():array;

		/**
		 * Doesn't return the descriptor but rather the concrete associated with inteface exported by given module
		*/
		protected function getModuleFor (string $interface):object {

			foreach ($this->getModules() as $descriptor)

				if ($interface == $descriptor->exportsImplements()) {

					$descriptor->warmModuleContainer();

					$descriptor->prepareToRun();

					return $descriptor->materialize();
				}
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}
	}
?>