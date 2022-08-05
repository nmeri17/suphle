<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Adapters\Console\SymfonyCli;

	use Suphle\Console\CliRunner;

	use Suphle\Testing\{Proxies\Extensions\FrontDoor, TestTypes\IsolatedComponentTest};

	use Suphle\Tests\Integration\Generic\CommonBinds;

	class EmptyModulesTest extends IsolatedComponentTest {

		use CommonBinds, SimpleCloneAssertions;

		private $consoleRunner;

		public function test_can_clone_without_modules () {

			$this->simpleCloneDependencies()->setConsoleRunner()

			->assertSimpleCloneModule();
		}

		/**
		 * Initialize cliRunner with no module
		*/
		protected function setConsoleRunner ():self {

			$this->consoleRunner = new CliRunner (

				new FrontDoor([]), new SymfonyCli("SuphleTest", "v2"),

				$this->fileConfig->getRootPath()
			);

			$this->consoleRunner->loadCommands();

			return $this;
		}
	}
?>