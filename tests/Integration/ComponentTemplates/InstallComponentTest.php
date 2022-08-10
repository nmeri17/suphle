<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\ComponentEjector;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Contracts\Config\ModuleFiles;

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Testing\{Condiments\FilesystemCleaner, TestTypes\CommandLineTest};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class InstallComponentTest extends CommandLineTest {

		protected const SUT_NAME = ExceptionComponentEntry::class;

		private $fileConfig, $container;

		use FilesystemCleaner;

		protected function setUp ():void {

			parent::setUp();

			$container = $this->container = $this->getContainer();

			$this->fileConfig = $container->getClass(ModuleFiles::class);
		}

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_install_component () {

			$this->assertInstalledComponent(

				$this->getComponentPath(), [], true
			);
		}

		protected function assertInstalledComponent (

			string $componentPath, array $commandOptions,

			bool $wipeWhenTrue
		):void {

			$this->assertEmptyDirectory($componentPath);

			$commandResult = $this->runInstallComponent(
				
				$componentPath, $commandOptions // given
			); // when

			// then
			$this->assertSame($commandResult, Command::SUCCESS );

			$this->assertNotEmptyDirectory($componentPath, $wipeWhenTrue);
		}

		protected function runInstallComponent (string $componentPath, array $commandOptions):int {

			$command = $this->consoleRunner->findHandler(

				InstallComponentCommand::commandSignature()
			);

			return (new CommandTester($command))

			->execute(array_merge([ // when

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			], $commandOptions));
		}

		protected function getComponentPath ():string {

			return $this->container->getClass(self::SUT_NAME)

			->userLandMirror();
		}

		public function test_will_not_override_existing () {

			$componentPath = $this->getComponentPath(); // given

			$this->assertInstalledComponent($componentPath, [], false);

			$parameters = $this->container->getMethodParameters(

				Container::CLASS_CONSTRUCTOR, self::SUT_NAME
			);

			$this->massProvide([

				self::SUT_NAME => $this->replaceConstructorArguments(

					self::SUT_NAME, $parameters, [], [

					"eject" => [0, []] // then
				])
			]);

			$this->runInstallComponent($componentPath, []); // when

			$this->assertNotEmptyDirectory($componentPath, true);
		}

		/**
		 * @dataProvider overrideOptions
		*/
		public function test_override_option_unserializes_properly (array $commandOptions, ?array $depositArguments) {

			$methodName = "depositFiles";

			$ejectorName = ComponentEjector::class;

			$this->container->whenTypeAny()->needsAny([

				$ejectorName => $this->replaceConstructorArguments(
					$ejectorName, [], [
						$methodName => true
				], [

					$methodName => [1, [$depositArguments]]
				])
			]);

			$this->assertInstalledComponent(

				$this->getComponentPath(), $commandOptions, false
			);
		}

		public function overrideOptions ():array {

			return [
				[[], []],
				[

					[InstallComponentCommand::OVERWRITE_OPTION], null
				],
				[

					[
						InstallComponentCommand::OVERWRITE_OPTION => self::SUT_NAME
					], [self::SUT_NAME]
				]
			];
		}
	}
?>