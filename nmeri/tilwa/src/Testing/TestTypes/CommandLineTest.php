<?php
	namespace Tilwa\Testing\TestTypes;

	use Tilwa\Adapters\Console\SymfonyCli;

	use Tilwa\Console\CliRunner;

	use Tilwa\Testing\Proxies\Extensions\FrontDoor;

	use Tilwa\Testing\Condiments\ModuleReplicator;

	use PHPUnit\Framework\TestCase;

	abstract class CommandLineTest extends TestVirginContainer {

		use ModuleReplicator;

		protected $consoleClient, $consoleRunner;

		protected function setUp ():void {

			$this->consoleClient = new SymfonyCli("SuphleTest", "v2");

			$this->consoleRunner = new CliRunner (new FrontDoor($this->getModules()), $this->consoleClient);

			$this->consoleRunner->loadCommands();
		}
		
		/**
		 * @return ModuleDescriptor[]
		 */
		abstract protected function getModules():array;
	}
?>