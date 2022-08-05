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

		private $sutSignature = "modules:create";

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

			$command = $this->consoleRunner->findHandler($this->sutSignature);

			// when
			return (new CommandTester($command))->execute([

				"template_folder" => $this->fileConfig->getRootPath() . "ModuleTemplate",

				"new_module_name" => $this->newModuleName
			]);
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