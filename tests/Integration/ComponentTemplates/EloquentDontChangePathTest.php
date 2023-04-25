<?php

namespace Suphle\Tests\Integration\ComponentTemplates;

use Suphle\Contracts\Config\{ComponentTemplates, Database};

use Suphle\File\FolderCloner;

use Suphle\Hydration\Container;

use Suphle\ComponentTemplates\{ComponentEjector, Commands\InstallComponentCommand};

use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

class EloquentDontChangePathTest extends InstallComponentTest
{
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

        return EloquentComponentEntry::class;
    }

    protected function getCommandOptions(array $otherOverrides = []): array
    {

        return array_merge([

            InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class,

            "--" .InstallComponentCommand::OVERWRITE_OPTION => [null] // without this, it won't try to eject
        ], $otherOverrides);
    }

    protected function componentIsInstalled(): bool // prevent it from overwriting our contents
    {return false;
    }

    public function test_writes_to_default_component_path()
    {

        $ejectorName = FolderCloner::class;

        $this->massProvide([

            $ejectorName => $this->replaceConstructorArguments(
                $ejectorName,
                [],
                [],
                [

                "transferFolder" => [1, [ // then

                    $this->anything(),

                    $this->getDefaultInstallLocation()
                ]]
                ]
            )
        ]);

        // when
        $this->assertInstalledComponent($this->getCommandOptions(), true);
    }

    protected function getDefaultInstallLocation(): string
    {

        return $this->getContainer()->getClass(Database::class)

        ->componentInstallPath();
    }
}
