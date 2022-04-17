<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

	use Tilwa\Bridge\Laravel\{Cli\ArtisanCli, LaravelAppConcrete};

	use Tilwa\Hydration\Container;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Illuminate\Database\Migrations\MigrationCreator;

	use Symfony\Component\Console\Tester\CommandTester;

	class ArtisanBridgeTest extends CommandLineTest {

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$laravelContainer = LaravelAppConcrete::class; // using the concrete since we need actual behavior by the time this reaches OrmLoader

					$laravelDouble = $this->positiveDouble($laravelContainer);

					$migrator = MigrationCreator::class;

					$laravelDouble->instance($migrator, $this->negativeDouble($migrator, [], [

							"create" => [1, []]
						])
					);

					$container->replaceWithConcrete($laravelContainer, $laravelDouble);
				})
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

			// then 2 => see module build
		}
	}
?>