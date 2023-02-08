<?php
	namespace Suphle\Tests\Integration\Modules\Cloning;

	use Suphle\Contracts\Config\{ModuleFiles, ComponentTemplates};

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Services\ComponentEntry as ServicesComponentEntry;

	use Suphle\File\FileSystemReader;

	use Suphle\Modules\{ModuleCloneService, ModulesBooter, Structures\ActiveDescriptors, Commands\CloneModuleCommand};

	use Suphle\Hydration\Container;

	use Suphle\Console\CliRunner;

	use Suphle\Testing\Condiments\FilesystemCleaner;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	trait SimpleCloneAssertions {

		use FilesystemCleaner;

		protected ModuleFiles $fileConfig;

		protected Container $container;

		protected CliRunner $consoleRunner;

		protected string $newModuleName = "ModuleAgnes",

		$sutName = CloneModuleCommand::class,

		$servicesTemplate = ServicesComponentEntry::class,

		$componentConfig = ComponentTemplates::class;

		protected function simpleCloneDependencies ():self {

			$this->container = $this->getContainer();

			$this->fileConfig = $this->container->getClass(ModuleFiles::class);

			return $this;
		}

		protected function replaceTemplateEntries ():void {

			$clonerServiceName = ModuleCloneService::class;

			$this->massProvide([

				$clonerServiceName => $this->positiveDouble(

					$clonerServiceName, [

						"bootNewlyCreatedContainer" => $this->returnCallback($this->bootNewlyCreatedContainer(...))
					], [],

					$this->container->getMethodParameters(

						Container::CLASS_CONSTRUCTOR, $clonerServiceName
					)
				)
			]);
		}

		protected function bootNewlyCreatedContainer (string $descriptorName):DescriptorInterface {

			$descriptor = new $descriptorName(new Container);

			$this->replaceConstructorArguments(ModulesBooter::class, [])

			->recursivelyBootModuleSet(

				new ActiveDescriptors([$descriptor])
			);

			$descriptor->getContainer()->whenTypeAny()

			->needsAny($this->newContainerBindings());

			return $descriptor;
		}

		protected function newContainerBindings ():array {

			return [

				$this->componentConfig => $this->positiveDouble($this->componentConfig, [

					"getTemplateEntries" => [$this->servicesTemplate]
				])
			];
		}

		protected function assertSimpleCloneModule (callable $onCloneSuccess = null):void {

			$this->replaceTemplateEntries();

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

				CloneModuleCommand::MODULE_NAME_ARGUMENT => $this->newModuleName,

				"--" . CloneModuleCommand::DESCRIPTOR_OPTION => $this->constructDescriptorName()
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

		protected function constructDescriptorName ():string {

			return implode("\\", [

				"\Suphle\Tests\Mocks\Modules", $this->newModuleName,

				"Meta", $this->newModuleName . "Descriptor"
			]);
		}
	}
?>