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

				$modulePath = $this->getModulePath(),

				$interfacePath = $this->moduleInterfacePath()
			);

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			if (!is_null($onCloneSuccess))

				$onCloneSuccess($modulePath);

			$this->assertNotEmptyDirectory($modulePath, true); // this inexplicably fails on random occassions

			$this->assertSavedFileNames([$interfacePath]);
		}

		protected function runSimpleCloneCommand (string $modulePath, string $interfacePath):int {

			if (file_exists($modulePath))

				$this->getFilesystemReader()->emptyDirectory($modulePath);
			
			if (file_exists($interfacePath))

				unlink($interfacePath);

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
		 * Gets the path to potential new module
		*/
		protected function getModulePath ():string {

			return $this->getFilesystemReader()->getAbsolutePath(

				$this->fileConfig->activeModulePath(),

				"../" .$this->newModuleName
			);
		}
		
		protected function moduleInterfacePath ():string {

			return implode("", [

				$this->fileConfig->getRootPath(),

				"Interactions", DIRECTORY_SEPARATOR,

				$this->newModuleName, ".php"
			]);
		}
	}
?>