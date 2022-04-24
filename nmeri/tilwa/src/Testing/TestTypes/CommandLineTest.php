<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Adapters\Console\SymfonyCli;

	use Tilwa\Console\CliRunner;

	use Tilwa\Hydration\Container;

	use Tilwa\Testing\Proxies\Extensions\FrontDoor;

	use Tilwa\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor, GagsException};

	abstract class CommandLineTest extends TestVirginContainer {

		use ModuleReplicator, BaseModuleInteractor, GagsException {

			GagsException::setUp as mufflerSetup;
		}

		protected $consoleRunner;

		protected function setUp ():void {

			$this->consoleRunner = new CliRunner (

				$this->entrance = new FrontDoor(
					$this->modules = $this->getModules(),

					$this->getEventParent()
				),
				new SymfonyCli("SuphleTest", "v2")
			);

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