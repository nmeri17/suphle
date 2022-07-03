<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Modules\ModuleDescriptor;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

	use Tilwa\Testing\Proxies\{ModuleHttpTest, ConfigureExceptionBridge, Extensions\FrontDoor};

	abstract class ModuleLevelTest extends TestVirginContainer {

		use ModuleReplicator, ConfigureExceptionBridge, ModuleHttpTest, BaseModuleInteractor {

			ConfigureExceptionBridge::setUp as mufflerSetup;
		}

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor(
				
				/*
				 Storing in an instance variable instead of reading directly from method so mutative methods can iterate and modify

				 Also, reading from getModules() with new ModuleDescriptor1 will return a new instance each time
				*/
				$this->modules = $this->getModules()
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

			foreach ($this->modules as $descriptor)

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