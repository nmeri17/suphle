<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\{ComponentTemplates, ModuleFiles};

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\{ComponentEjector, Commands\InstallComponentCommand};

	use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

	use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Suphle\Tests\Mocks\Models2\User as GeneratedUser; // refrain from using Models so it doesn't get whacked

	class EloquentChangePathTest extends InstallComponentTest {

		protected Container $container;

		protected function setUp ():void {

			parent::setUp();

			$this->container = $this->getContainer();
		}

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$config = ComponentTemplates::class;

					$container->replaceWithMock($config, $config, [

						"getTemplateEntries" => [

							$this->componentEntry()
						]
					]);
				})
			];
		}

		protected function componentEntry ():string {

			return EloquentComponentEntry::class;
		}

		public function test_correctly_writes_to_database_folder () {

			$destination = $this->getModelDirectory();

			$commandOptions = $this->getCommandOptions([ // given

				"--" . InstallComponentCommand::COMPONENT_ARGS_OPTION =>

				EloquentComponentEntry::EJECT_DESTINATION . "=". $destination. " ".

				EloquentComponentEntry::EJECT_NAMESPACE . "=". "Suphle\Tests\Mocks\Models2"
			]);

			$this->assertInstalledComponent($commandOptions, true/*for further assertions*/); // when

			$this->assertTrue(class_exists(GeneratedUser::class)); // then

			$this->assertNotEmptyDirectory($destination, true);

			// then // confirm it doesnt override
		}

		protected function getModelDirectory ():string {

			return $this->container->getClass(ModuleFiles::class)

			->getRootPath(). "Models2". DIRECTORY_SEPARATOR;
		}

		protected function getComponentPath ():string { // overriding this since its hydration already occurs on the test ie before the ejector can pass our arguments to it. Without it, it will incorrectly be declared absent/present

			return $this->getModelDirectory();
		}

		protected function getCommandOptions (array $otherOverrides = []):array {

			return array_merge([

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			], $otherOverrides);
		}
	}
?>