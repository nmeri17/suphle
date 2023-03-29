<?php
	namespace Suphle\Tests\Integration\Modules\Cloning;

	use Suphle\Hydration\Container;

	use Suphle\Modules\ModuleCloneService;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Contracts\Modules\DescriptorInterface;

	use Suphle\Bridge\Laravel\ComponentEntry as LaravelComponentEntry;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Integration\ComponentTemplates\ExceptionComponentTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class CloneAndInstallTest extends CommandLineTest {

		use SimpleCloneAssertions {

			SimpleCloneAssertions::newContainerBindings as inheritedContainerBindings;
		}

		protected function setUp ():void {

			parent::setUp();

			$this->simpleCloneDependencies();

			/**
			* This is useful when the test is executed in parallel. Without it, we run into file-system permission issues
			* 
			* @see https://github.com/paratestphp/paratest/issues/748#issuecomment-1486614516
			*/
			$this->file = __DIR__ . "/test_file_" . sha1(uniqid(__METHOD__));
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor (new Container)];
		}

		/**
		 * @Depends FolderClonerTest::test_can_transfer_files_to_current_location
		 * @Depends ExceptionComponentTest::test_can_install_component
		*/
		public function test_clone_will_install_templates () {

			$this->assertSimpleCloneModule();
		}

		protected function newContainerBindings ():array {

			return array_merge($this->inheritedContainerBindings(), [

				$this->servicesTemplate => $this->negativeDouble($this->servicesTemplate, [], [ // or replaceConstructorArguments

					"eject" => [1, []]
				])
			]);
		}
	}
?>