<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\Testing\TestTypes\CommandLineTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class FolderClonerTest extends CommandLineTest {

		use SimpleCloneAssertion;

		protected const SUT_SIGNATURE = "modules:create";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_transfer_files_to_current_location () {

			$this->simpleCloneDependencies()->assertClonedModule();
		}

		public function test_correctly_changes_file_contents () {

			//
		}

		public function test_correctly_changes_file_folder_names () {

			//
		}
	}
?>