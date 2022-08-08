<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Contracts\Config\ModuleFiles;

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

			$this->entrance = new FrontDoor(
					
				$this->modules = $this->getModules()
			);

			$this->provideTestEquivalents();

			$this->monitorModuleContainers();

			$this->consoleRunner = new CliRunner (

				$this->entrance, new SymfonyCli("SuphleTest", "v2")
			);

			$this->consoleRunner->extractAvailableCommands()
			
			->setRootPath( // can't pass this into the constructor since as evident here, we can't have access to any dynamic paths until containers have booted
				$this->getContainer()->getClass(ModuleFiles::class)

				->getRootPath()
			)
			->funnelToClient();

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