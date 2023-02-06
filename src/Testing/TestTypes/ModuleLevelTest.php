<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Modules\{ModuleDescriptor, Structures\ActiveDescriptors};

	use Suphle\Hydration\Container;

	use Suphle\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

	use Suphle\Testing\Proxies\{ModuleHttpTest, ConfigureExceptionBridge, Extensions\FrontDoor};

	abstract class ModuleLevelTest extends TestVirginContainer {

		use ModuleReplicator, ConfigureExceptionBridge, ModuleHttpTest, BaseModuleInteractor {

			ConfigureExceptionBridge::setUp as mufflerSetup;
		}

		protected bool $useTestComponents = true;

		protected function setUp ():void {

			$entrance = $this->entrance = new FrontDoor(
				
				/*
				 Storing in an instance variable instead of reading directly from method so mutative methods can iterate and modify

				 Also, reading from getModules() with new ModuleDescriptor1 will return a new instance each time
				*/
				$this->modules = $this->getModules()
			);

			if ($this->useTestComponents)

				$this->provideTestEquivalents();

			$this->bootMockEntrance($entrance);

			$this->mufflerSetup(); // useTestComponents shouldn't prevent this because then, we can't even tell why request possibly failed
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules ():array;

		/**
		 * Doesn't return the descriptor but rather the concrete associated with inteface exported by given module
		*/
		protected function getModuleFor (string $interface):object {

			$descriptor = (new ActiveDescriptors($this->modules))

			->findMatchingExports($interface);

			$descriptor->warmModuleContainer();

			$descriptor->prepareToRun();

			return $descriptor->materialize();
		}

		protected function getContainer ():Container {

			return $this->activeModuleContainer();
		}
	}
?>