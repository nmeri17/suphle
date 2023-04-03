<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\{ComponentTemplates, ModuleFiles};

	use Suphle\File\FolderCloner;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\{ComponentEjector, Commands\InstallComponentCommand};

	use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

	use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class EloquentDontChangePathTest extends InstallComponentTest {

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

		protected function getCommandOptions (array $otherOverrides = []):array {

			return array_merge([

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			], $otherOverrides);
		}

		/**
		 * We can afford to overwrite it since the actual value points to AppModels which is the production directory and is different from what we're using ie eligible for wiping
		 * 
		protected function componentIsInstalled ():bool { // prevent it from overwriting our contents

			return false;
		}*/

		public function test_writes_to_default_component_path () {

			$methodName = "transferFolder";

			$ejectorName = FolderCloner::class;

			$this->container->whenTypeAny()->needsAny([

				$ejectorName => $this->replaceConstructorArguments(
					$ejectorName, [], [], [

					$methodName => [1, [ // then

						$this->anything(),

						$this->getDefaultInstallLocation()
					]]
				])
			]);

			// when
			$this->assertInstalledComponent($this->getCommandOptions(), true);
		}

		protected function getDefaultInstallLocation ():string {

			return $this->replaceConstructorArguments($this->componentEntry(), [ // don't use container since this very object will trigger its hydration

				ModuleFiles::class => $this->container->getClass(ModuleFiles::class)
			])
			->defaultInstallPath();
		}
	}
?>