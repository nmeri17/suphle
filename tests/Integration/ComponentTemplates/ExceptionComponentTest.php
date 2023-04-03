<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\{ComponentEjector, Commands\InstallComponentCommand};

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class ExceptionComponentTest extends InstallComponentTest {

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

			return ExceptionComponentEntry::class;
		}

		public function test_can_install_component () {

			$this->assertInstalledComponent($this->getCommandOptions());
		}

		public function test_will_not_override_existing () {

			$entryClass = $this->componentEntry();

			$commandOptions = $this->getCommandOptions();

			$this->assertInstalledComponent($commandOptions); // given

			$parameters = $this->container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, $entryClass
			);

			$this->massProvide([

				$entryClass => $this->replaceConstructorArguments(

					$entryClass, $parameters, [], [

					"eject" => [0, []] // then
				])
			]);

			$this->runInstallComponent($commandOptions); // when
		}

		/**
		 * @dataProvider overrideOptions
		*/
		public function test_override_option_unserializes_properly (array $customOptions, ?array $depositArguments) {

			$methodName = "depositFiles";

			$ejectorName = ComponentEjector::class;

			$this->container->whenTypeAny()->needsAny([

				$ejectorName => $this->replaceConstructorArguments(
					$ejectorName, [], [
						$methodName => true // given
				], [

					$methodName => [1, [$depositArguments]] // then
				])
			]);

			$commandOptions = $this->getCommandOptions($customOptions);

			// when
			$this->assertInstalledComponent($commandOptions, true);

			$this->container->refreshClass($ejectorName); // flush mock

			$this->runInstallComponent( $commandOptions); // re-set the files there since the previous command didn't write anything to disk, while above assertInstalledComponent deleted them
		}

		protected function getCommandOptions (array $otherOverrides = []):array {

			return array_merge([

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			], $otherOverrides);
		}
	}
?>