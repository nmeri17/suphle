<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Testing\TestTypes\CommandLineTest;

	use Tilwa\Bridge\Laravel\Cli\ArtisanCli;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Hydration\Container;

	use Illuminate\Database\Migrations\Migrator;

	use Symfony\Component\Console\Tester\CommandTester;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	class ArtisanBridgeTest extends CommandLineTest {

		protected function getModules():array {

			return [
				new ModuleOneDescriptor ($this->providedContainer())
			];
		}

		public function test_can_create_migrations () {

			// given => Artisan command is set in default command list

			$command = $this->consoleRunner->findHandler("bridge:laravel");

			$commandTester = new CommandTester($command);

			$commandTester->execute([ // when

				"to_forward" => "make:migration create_users_table --path=/Migrations",
			]);

			// then 1
			$commandTester->assertCommandIsSuccessful(); // $commandTester::getDisplay can be used to extract console output as a string

			// then 2 => see [providedContainer]
		}

		private function providedContainer ():Container {

			$container = new Container;

			$migrator = Migrator::class;

			$container->getClass(LaravelContainer::class)
			
			->instance($migrator, $this->negativeDouble($migrator, [], [

					"create" => [1, []]
				])
			);

			return $container;
		} 
	}
?>