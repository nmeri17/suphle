<?php
	namespace Suphle\Tests\Integration\Modules\Cloning;

	use Suphle\Hydration\Container;

	use Suphle\Services\ComponentEntry as ServicesComponentEntry;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Testing\TestTypes\CommandLineTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Symfony\Component\Console\Command\Command;

	class ComponentArgumentTest extends CommandLineTest {

		use SimpleCloneAssertions {

			SimpleCloneAssertions::newContainerBindings as inheritedContainerBindings;
		}

		protected function setUp ():void {

			parent::setUp();

			$this->simpleCloneDependencies();

			$this->file = __DIR__ . "/test_file_" . sha1(uniqid(__METHOD__));
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor (new Container)];
		}

		public function test_clone_will_install_templates () {

			$this->replaceTemplateEntries();

			$commandResult = $this->executeCloneCommand([ // given

				"--". InstallComponentCommand::COMPONENT_ARGS_OPTION => "foo=value uju=bar"
			]);

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			$this->assertNotEmptyDirectory($this->getModulePath(), true);

			$this->assertSavedFileNames([$this->moduleInterfacePath()]);
		}

		protected function newContainerBindings ():array {

			return array_merge($this->inheritedContainerBindings(), [

				ServicesComponentEntry::class => $this->negativeDouble(ServicesComponentEntry::class, [], [ // or replaceConstructorArguments

					"setInputArguments" => [1, [

						["foo" => "value", "uju" => "bar"]
					]]
				])
			]);
		}
	}
?>