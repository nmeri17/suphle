<?php
	namespace Suphle\Tests\Integration\Modules\Cloning;

	use Suphle\Hydration\Container;

	use Suphle\Testing\TestTypes\CommandLineTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class FolderClonerTest extends CommandLineTest {

		use SimpleCloneAssertions;

		protected function setUp ():void {

			parent::setUp();

			$this->simpleCloneDependencies();
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_transfer_files_to_current_location () {

			$this->assertSimpleCloneModule();
		}

		public function test_correctly_changes_file_folder_names () {

			$this->assertSimpleCloneModule(function ($modulePath) {

				$descriptorFullName = implode("\\", [

					"\Suphle\Tests\Mocks\Modules", $this->newModuleName,

					"Meta", $this->newModuleName . "Descriptor"
				]);

				$descriptorPath = implode(DIRECTORY_SEPARATOR, [

					$modulePath, "Meta",

					$this->newModuleName . "Descriptor.php"
				]);

				$this->assertFileExists($descriptorPath);

				$instance = new $descriptorFullName(new Container);

				// try to boot it
				$instance->warmModuleContainer();

				$instance->prepareToRun();
			});
		}
	}
?>