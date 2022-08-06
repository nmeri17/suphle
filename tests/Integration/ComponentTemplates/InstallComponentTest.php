<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Modules\Commands\InstallComponentCommand;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\FileSystemReader;

	use Suphle\Testing\{Condiments\FilesystemCleaner, TestTypes\CommandLineTest};

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class InstallComponentTest extends CommandLineTest {

		private $fileConfig;

		protected function setUp ():void {

			parent::setUp();

			$this->fileConfig = $this->getContainer()->getClass(ModuleFiles::class);
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_install_component () {

			$this->assertInstalledComponent([]);
		}

		protected function assertInstalledComponent (array $commandOptions, callable $onInstallSuccess = null):void {

			$commandResult = $this->runInstallComponent(
				$componentPath = $this->fileConfig->activeModulePath().

				DIRECTORY_SEPARATOR ., // still pending
				$commandOptions
			); // given

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			if (!is_null($onInstallSuccess))

				$onInstallSuccess($componentPath);

			$this->assertNotEmptyDirectory($componentPath, true);
		}

		protected function runInstallComponent (string $componentPath, array $commandOptions):int {

			$this->assertEmptyDirectory($componentPath);

			$command = $this->consoleRunner->findHandler(

				InstallComponentCommand::commandSignature()
			);

			return (new CommandTester($command))

			->execute(array_merge([ // when

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			], $commandOptions));
		}

		public function test_will_not_override_existing () {

			$this->assertInstalledComponent([

			//	InstallComponentCommand::OVERWRITE_OPTION
			]);
			// confirm it's not on the list
		}

		public function test_can_override_existing__all () {

			// duplicate folder. copy to same folder with different name and work with that
		}

		public function test_can_override_existing__some () {

			//
		}
	}
?>