<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Suphle\Exception\ComponentEntry as ExceptionComponentEntry;

	use Suphle\Bridge\Laravel\ComponentEntry as LaravelComponentEntry;

	use Suphle\Services\ComponentEntry as ServicesComponentEntry;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class GenericComponentTest extends CommandLineTest {

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$config = ComponentTemplates::class;

					$container->replaceWithMock($config, $config, [

						"getTemplateEntries" => $this->componentList()
					]);
				})
			];
		}

		public function test_can_install_all_components () {

			// given @see => default module config

			$command = $this->consoleRunner->findHandler(

				InstallComponentCommand::commandSignature()
			);

			$result = (new CommandTester($command))

			->execute([ // when

				InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
			]);

			$this->assertSame(Command::SUCCESS, $result); // sanity check

			$this->assertInstalledAll();
		}

		protected function assertInstalledAll ():void {

			$this->assertSame( // then

				count($this->componentList()),

				count($this->getInstalledComponents())
			);
		}

		protected function getInstalledComponents ():array {

			$container = $this->getContainer();

			$componentInstances = array_map(function ($entry) use ($container) {

				return $container->getClass($entry);
			}, $this->componentList());

			return array_filter($componentInstances, function ($entry) {

				return $entry->hasBeenEjected();
			});
		}

		protected function componentList ():array {

			return [
				ExceptionComponentEntry::class,

				LaravelComponentEntry::class,

				ServicesComponentEntry::class
			]; // excluding EloquentComponentEntry since we're using a different name than AppModels
		}
	}
?>