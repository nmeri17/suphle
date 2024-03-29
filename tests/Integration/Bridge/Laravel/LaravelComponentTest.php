<?php

namespace Suphle\Tests\Integration\Bridge\Laravel;

use Suphle\Contracts\{Config\ComponentTemplates, Bridge\LaravelContainer};

use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

use Suphle\Bridge\Laravel\ComponentEntry as LaravelComponentEntry;

use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Throwable;
use RuntimeException;

class LaravelComponentTest extends InstallComponentTest
{
    private $container;

    protected function setUp(): void
    {

        $this->markTestSkipped(
            "Using this cuz this test should be ran specially. It relies on a very fragile filesystem io that fails at will, thereby causing all other tests to fail with it.
				* 
				* Whenever it's ran on its own and filesystem fails as usual, maybe due to permission issues, it'll leave a backup behind that refuses to return to original location. Comment out the hydration of OrmBridge in moduleOneDescriptor and rerun the test
				* 
				* To run the test itself, comment out this skip"
        );

        parent::setUp();

        $this->container = $this->getContainer();
    }

    protected function getModules(): array
    {

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

    protected function componentEntry(): string
    {

        return LaravelComponentEntry::class;
    }

    public function test_can_install_component()
    {

        $isAlreadyInstalled = $this->componentIsInstalled();

        $componentPath = $this->getComponentPath();

        $backupPath = __DIR__ . DIRECTORY_SEPARATOR . "backup";

        $fileSystemReader = $this->getFilesystemReader();

        if ($isAlreadyInstalled) {

            $fileSystemReader->deepCopy($componentPath, $backupPath);

            $fileSystemReader->emptyDirectory($componentPath);
        }

        $this->container->refreshClass(LaravelContainer::class); // prevent the influence of any instance that was loaded during module booting

        try {

            $this->container->getClass(LaravelContainer::class);
        } catch (Throwable $exception) {

            $this->assertInstanceOf(RuntimeException::class, $exception); // can't use expectException since that requires test termination and would prevent below from running

            $this->assertInstalledComponent([

                InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
            ]); // when

            $this->assertInstanceOf( // then

                LaravelContainer::class,
                $this->container->getClass(LaravelContainer::class)
            );
        } finally {

            foreach ([$componentPath, $backupPath] as $path) {

                if (!file_exists($path)) {
                    return;
                }
            }

            if ($isAlreadyInstalled) {

                $fileSystemReader->emptyDirectory($componentPath);

                $fileSystemReader->deepCopy($backupPath, $componentPath);

                $fileSystemReader->emptyDirectory($backupPath);
            }
        }
    }
}
