<?php
	namespace Suphle\Tests\Integration\Modules\Cloning;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\File\FileSystemReader;

	use Suphle\Modules\Commands\CloneModuleCommand;

	use Suphle\Testing\Condiments\FilesystemCleaner;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	trait SimpleCloneAssertions {

		use FilesystemCleaner;

		protected $fileConfig, $container, $newModuleName = "ModuleAgnes",

		$sutName = CloneModuleCommand::class;

		protected function simpleCloneDependencies ():self {

			$this->container = $this->getContainer();

			$this->fileConfig = $this->container->getClass(ModuleFiles::class);

			return $this;
		}

		protected function assertSimpleCloneModule (callable $onCloneSuccess = null):void {

			$commandResult = $this->runSimpleCloneCommand( // given

				$modulePath = $this->getModulePath()
			);

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			if (!is_null($onCloneSuccess))

				$onCloneSuccess($modulePath);

			$this->assertNotEmptyDirectory($modulePath, true);
		}

		protected function runSimpleCloneCommand (string $modulePath):int {

			$this->assertEmptyDirectory($modulePath);

			$command = $this->consoleRunner->findHandler(

				CloneModuleCommand::commandSignature()
			);

			// when
			return (new CommandTester($command))->execute([

				CloneModuleCommand::SOURCE_ARGUMENT => $this->fileConfig->getRootPath() . "ModuleTemplate",

				CloneModuleCommand::MODULE_NAME_ARGUMENT => $this->newModuleName
			]);
		}
		
		/**
		 * Gets the path containing all modules
		*/
		protected function getModulePath ():string {

			return $this->container->getClass(FileSystemReader::class)

			->getAbsolutePath(

				$this->fileConfig->activeModulePath(),

				"../" .$this->newModuleName
			);
		}
	}
?>