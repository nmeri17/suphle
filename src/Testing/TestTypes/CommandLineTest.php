<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Adapters\Console\SymfonyCli;

	use Suphle\Console\CliRunner;

	use Suphle\Hydration\Container;

	use Suphle\Testing\Proxies\{Extensions\FrontDoor, ConfigureExceptionBridge};

	use Suphle\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

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