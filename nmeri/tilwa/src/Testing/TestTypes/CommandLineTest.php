<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Adapters\Console\SymfonyCli;

	use Tilwa\Console\CliRunner;

	use Tilwa\Testing\Proxies\Extensions\FrontDoor;

	use Tilwa\Testing\Condiments\{ModuleReplicator, BaseModuleInteractor, GagsException};

	use PHPUnit\Framework\TestCase;

	abstract class CommandLineTest extends TestVirginContainer {

		use ModuleReplicator, BaseModuleInteractor, GagsException {

			GagsException::setUp as mufflerSetup;
		}

		private $modules; // trait will access this

		protected $consoleRunner;

		protected function setUp ():void {

			$this->consoleRunner = new CliRunner (

				new FrontDoor($this->modules = $this->getModules()),

				new SymfonyCli("SuphleTest", "v2")
			);

			$this->consoleRunner->loadCommands();

			$this->mufflerSetup();
		}
		
		/**
		 * @return DescriptorInterface[]
		 */
		abstract protected function getModules ():array;
	}
?>