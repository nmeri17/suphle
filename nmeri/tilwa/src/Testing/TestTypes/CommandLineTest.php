<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Adapters\Console\SymfonyCli;

	use Tilwa\Console\CliRunner;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Proxies\{Extensions\FrontDoor, ConfigureExceptionBridge};

	use Tilwa\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

	abstract class CommandLineTest extends TestVirginContainer {

		use ModuleReplicator, BaseModuleInteractor, ConfigureExceptionBridge {

			ConfigureExceptionBridge::setUp as mufflerSetup;
		}

		protected $consoleRunner, $modules;

		protected function setUp ():void {

			$this->consoleRunner = new CliRunner (

				$this->entrance = new FrontDoor(
					
					$this->modules = $this->getModules()
				),
				new SymfonyCli("SuphleTest", "v2")
			);

			$this->provideTestEquivalents();

			$this->bootMockEntrance($this->entrance);

			$this->consoleRunner->loadCommands();

			$this->mufflerSetup();
		}
		
		/**
		 * @return DescriptorInterface[]
		 */
		abstract protected function getModules ():array;

		protected function getContainer ():Container {

			return current($this->modules)->getContainer();
		}
	}
?>