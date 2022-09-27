<?php
	namespace Suphle\Tests\Integration\ComponentTemplates;

	use Suphle\Contracts\Config\ComponentTemplates;

	use Suphle\Hydration\Container;

	use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

	use Suphle\Testing\TestTypes\CommandLineTest;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

	class GenericComponentTest extends CommandLineTest {

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container)];
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

			$container = $this->getContainer();

			foreach (
				$container->getClass(ComponentTemplates::class)

				->getTemplateEntries() as $entry
			)

				$this->assertTrue( // then

					$container->getClass($entry)->hasBeenEjected()
				);
		}
	}
?>