<?php

namespace Suphle\Tests\Integration\Bridge\Laravel;

use Suphle\Hydration\Container;

use Suphle\Contracts\Bridge\LaravelContainer;

use Suphle\Bridge\Laravel\Cli\ArtisanCli;

use Suphle\Testing\{TestTypes\CommandLineTest, Condiments\FilesystemCleaner};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Illuminate\Database\MigrationServiceProvider;

use Symfony\Component\Console\Tester\CommandTester;

class ArtisanBridgeTest extends CommandLineTest
{
    use FilesystemCleaner;

    protected const MIGRATION_FOLDER = "sample_migrations";

    protected function getModules(): array
    {

        return [new ModuleOneDescriptor(new Container()) ];
    }

    public function test_can_create_migrations()
    {

        // given => migrator command is wired in during laravel booting in artisan environment

        $migrationPath = $this->migrationPath();

        $this->assertEmptyDirectory($migrationPath); // I would've liked to replace migrator instance injected in MigrationServiceProvider with a mock, but that replacement hasn't been possible

        $command = $this->consoleRunner->findHandler(ArtisanCli::commandSignature());

        $commandTester = new CommandTester($command);

        $commandTester->execute([ // when

            ArtisanCli::TO_FORWARD_ARGUMENT => "make:migration create_users_table --path=" . self::MIGRATION_FOLDER,
        ]);

        // then
        $commandTester->assertCommandIsSuccessful(); // $commandTester::getDisplay can be used to extract console output as a string

        $this->assertNotEmptyDirectory($migrationPath, true);
    }

    private function migrationPath(): string
    {

        return $this->firstModuleContainer()->getClass(LaravelContainer::class)

        ->basePath() . DIRECTORY_SEPARATOR . self::MIGRATION_FOLDER;
    }
}
