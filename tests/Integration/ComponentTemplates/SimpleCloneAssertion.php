<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\FileSystemReader;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Testing\Condiments\FilesystemCleaner;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	trait SimpleCloneAssertion {

		use FilesystemCleaner;

		protected $fileConfig, $container, $newModuleName = "ModuleAgnes",

		$sutName = CloneModuleCommand::class;

		protected function simpleCloneDependencies ():self {

			$this->container = $this->getContainer();

			$this->fileConfig = $this->container->getClass(ModuleFiles::class);

			return $this;
		}

		protected function assertClonedModule ():void {

			$modulePath = $this->getModulePath();

			$this->assertEmptyDirectory($modulePath); // given

			$command = $this->consoleRunner->findHandler(self::SUT_SIGNATURE);

			// when
			$commandResult = (new CommandTester($command))->execute([

				"template_folder" => $this->fileConfig->getRootPath() . "ModuleTemplate",

				"new_module_name" => $this->newModuleName
			]);

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			$this->assertNotEmptyDirectory($modulePath, true);
		}

		protected function getModulePath ():string {

			return $this->container->getClass(FileSystemReader::class)

			->getAbsolutePath(

				$this->fileConfig->activeModulePath(),

				"../" .$this->newModuleName
			);
		}
	}
?>