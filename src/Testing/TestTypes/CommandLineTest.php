<?php
	namespace Suphle\Testing\TestTypes;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Adapters\Console\SymfonyCli;

	use Suphle\Console\{CliRunnerAccessor, CliRunner};

	use Suphle\Hydration\Container;

	use Suphle\Testing\Proxies\{Extensions\FrontDoor, ConfigureExceptionBridge};

	use Suphle\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor};

	abstract class CommandLineTest extends TestVirginContainer {

		use ModuleReplicator, BaseModuleInteractor, ConfigureExceptionBridge {

			ConfigureExceptionBridge::setUp as mufflerSetup;
		}

		protected CliRunner $consoleRunner;

		protected function setUp ():void {

			$this->entrance = new FrontDoor(
					
				$this->modules = $this->getModules()
			);

			$this->provideTestEquivalents();

			$this->monitorModuleContainers();

			$runnerAccessor = new CliRunnerAccessor (

				$this->entrance, "SuphleTest", true
			);

			$runnerAccessor->forwardCommandsToRunner(

				$this->getContainer()->getClass(ModuleFiles::class)

				->getRootPath()
			);

			$this->consoleRunner = $runnerAccessor->getRunner();

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