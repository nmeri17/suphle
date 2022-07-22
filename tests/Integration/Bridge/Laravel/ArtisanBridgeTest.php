<?php
	namespace Suphle\Tests\Integration\Bridge\Laravel;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Bridge\LaravelContainer;

	use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer, Condiments\FilesystemCleaner};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use Illuminate\Database\MigrationServiceProvider;

	use Symfony\Component\Console\Tester\CommandTester;

	class ArtisanBridgeTest extends CommandLineTest {

		use FilesystemCleaner;

		private $migrationFolder = "sample_migrations";

		protected function getModules ():array {

			return [new ModuleOneDescriptor(new Container) ];
		}

		public function test_can_create_migrations () {

			// given => migrator command is wired in during laravel booting in artisan environment

			$migrationPath = $this->migrationPath();

			$this->assertEmptyDirectory($migrationPath); // I would've liked to replace migrator instance injected in MigrationServiceProvider with a mock, but that replacement hasn't been possible

			$command = $this->consoleRunner->findHandler("bridge:laravel");

			$commandTester = new CommandTester($command);

			$commandTester->execute([ // when

				"to_forward" => "make:migration create_users_table --path=" . $this->migrationFolder,
			]);

			// then
			$commandTester->assertCommandIsSuccessful(); // $commandTester::getDisplay can be used to extract console output as a string

			$this->assertNotEmptyDirectory($migrationPath);

			$this->emptyDirectory($migrationPath);
		}

		private function migrationPath ():string {

			return $this->firstModuleContainer()->getClass(LaravelContainer::class)

			->basePath() . DIRECTORY_SEPARATOR . $this->migrationFolder;
		}
	}
?>