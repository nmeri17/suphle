<?php

namespace Suphle\Tests\Integration\Routing\Crud;

use Suphle\Routing\Commands\CrudCommand;

use Suphle\File\FolderCloner;

use Suphle\Contracts\Config\ModuleFiles;

use Suphle\Hydration\Container;

use Suphle\Console\BaseCliCommand;

use Suphle\Testing\TestTypes\CommandLineTest;

use Suphle\Tests\Integration\Generic\TestsModuleList;

use Suphle\Tests\Mocks\Interactions\ModuleThree;

use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\FlavorCoordinator, PayloadReaders\BaseFlavorBuilder};

use Suphle\Tests\Mocks\Modules\ModuleThree\{Coordinators\FlavorCoordinator as ModuleThreeCoordinator, PayloadReaders\BaseFlavorBuilder as ModuleThreeBuilder};

use Suphle\Tests\Mocks\Models\Eloquent\Flavor;

use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

class CrudCommandTest extends CommandLineTest
{
    use TestsModuleList;

    protected string $resourceName = "Flavor";

    protected function setUp(): void
    {

        $this->setModuleOne();

        $this->setModuleThree();

        parent::setUp();

        $this->file = __DIR__ . "/test_file_" . sha1(uniqid(__METHOD__));
    }

    protected function getModules(): array
    {

        return [$this->moduleOne, $this->moduleThree];
    }

    protected function tearDown(): void
    {

        $this->deleteCreatedResourceFiles();
    }

    protected function deleteCreatedResourceFiles(?Container $container = null): void
    {

        if (is_null($container)) {
            $container = $this->getContainer();
        }

        foreach (
            $container->getClass(FolderCloner::class)

            ->getCopiedFiles() as $filePath
        ) {

            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function test_correctly_parses_resource_templates()
    {

        // given // the template files to be copied

        $this->assertSame($this->executeCrudCommand(), Command::SUCCESS); // then

        $this->assertCreatedClasses([

            FlavorCoordinator::class, BaseFlavorBuilder::class
        ]);
    }

    protected function executeCrudCommand(array $additionalArguments = []): int
    {

        $command = $this->consoleRunner->findHandler(
            CrudCommand::commandSignature()
        );

        return (new CommandTester($command))->execute(array_merge([ // when

            CrudCommand::RESOURCE_NAME_ARGUMENT => $this->resourceName
        ], $additionalArguments));
    }

    protected function assertCreatedClasses(array $classNames): void
    {

        $classesExist = false;

        foreach ($classNames as $className) {

            if (!$classesExist = class_exists($className)) {
                break;
            }
        }

        $this->assertTrue($classesExist);
    }

    public function test_writes_to_the_relevant_locations()
    {

        $this->executeCrudCommand();

        // then
        $this->assertTrue(class_exists(Flavor::class));

        $this->assertFileExists($this->getSampleViewFile());
    }

    protected function getSampleViewFile(): string
    {

        $markupPath = $this->getContainer()

        ->getClass(ModuleFiles::class)->defaultViewPath();

        return implode(DIRECTORY_SEPARATOR, [

            $markupPath, trim($this->resourceName, "\\"),

            "create-form.blade.php"
        ]);
    }

    public function test_api_option_doesnt_output_views()
    {

        $this->executeCrudCommand([

            "--". CrudCommand::IS_API_OPTION => true
        ]);

        $this->assertFileDoesNotExist($this->getSampleViewFile()); // then
    }

    public function test_with_module_name_writes_to_that_module()
    {

        $this->executeCrudCommand([

            "--". BaseCliCommand::HYDRATOR_MODULE_OPTION => ModuleThree::class
        ]);

        $this->assertCreatedClasses([

            ModuleThreeCoordinator::class, ModuleThreeBuilder::class
        ]);

        $this->deleteCreatedResourceFiles($this->getContainerFor(ModuleThree::class));
    }
}
