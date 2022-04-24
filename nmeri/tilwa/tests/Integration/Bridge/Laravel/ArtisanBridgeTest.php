<?php
	namespace Tilwa\Tests\Integration\Bridge\Laravel;

	use Tilwa\Hydration\Container;

	use Tilwa\Contracts\Bridge\LaravelContainer;

	use Tilwa\Testing\TestTypes\CommandLineTest;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

	use org\bovigo\vfs\{vfsStream, vfsStreamDirectory};

	use Illuminate\Database\MigrationServiceProvider;

	use Symfony\Component\Console\Tester\CommandTester;

	class ArtisanBridgeTest extends CommandLineTest {

		private $root, $migrationFolder = "sample_migrations";

		protected function setUp ():void {

			parent::setUp();

			$this->root = vfsStream::setup($this->laravelBasePath());
		}

		protected function getModules ():array {

			return [

				new ModuleOneDescriptor (new Container)
			];
		}

		public function test_can_create_migrations () {

			// given => migrator command is wired in during laravel booting in artisan environment
/*var_dump($this->laravelBasePath(), $this->root->url(), $this->root->hasChild($this->migrationFolder));
			$this->assertFalse($this->root->hasChild($this->migrationFolder));*/

			$command = $this->consoleRunner->findHandler("bridge:laravel");

			$commandTester = new CommandTester($command);

			$commandTester->execute([ // when

				"to_forward" => "make:migration create_users_table --path=" . $this->migrationFolder, // I would've liked to replace migrator instance injected in MigrationServiceProvider with a mock, but that replacement hasn't been possible
			]);

			// then
			$commandTester->assertCommandIsSuccessful(); // $commandTester::getDisplay can be used to extract console output as a string

			$this->assertTrue($this->root->hasChild($this->migrationFolder));
		}

		private function laravelBasePath ():string {

			return $this->firstModuleContainer()->getClass(LaravelContainer::class)->basePath();
		}
	}
?>