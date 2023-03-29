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

			$this->file = __DIR__ . "/test_file_" . sha1(uniqid(__METHOD__));
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_transfer_files_to_current_location () {

			$this->assertSimpleCloneModule();
		}

		/**
		 * @depends test_can_transfer_files_to_current_location
		*/
		public function test_correctly_changes_file_folder_names () {

			$this->assertSimpleCloneModule(function ($modulePath) {

				$descriptorPath = implode(DIRECTORY_SEPARATOR, [

					$modulePath, "Meta",

					$this->newModuleName . "Descriptor.php"
				]);

				$this->assertFileExists($descriptorPath);

				$descriptorFullName = $this->constructDescriptorName();

				$instance = new $descriptorFullName(new Container);

				// try to boot it
				$instance->warmModuleContainer();

				$instance->prepareToRun();
			});
		}
	}
?>