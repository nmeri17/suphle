<?php

namespace Suphle\Tests\Integration\ComponentTemplates;

use Suphle\Contracts\Config\{ComponentTemplates, Database};

use Suphle\Contracts\IO\EnvAccessor;

use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

use Suphle\Testing\{ TestTypes\InstallComponentTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\DatabaseMock};

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Suphle\Tests\Integration\ComponentTemplates\Models\User as GeneratedUser;

class EloquentActualPathTest extends InstallComponentTest
{
    protected string $destinationFolder = "Models";

    protected function getModules(): array
    {

        return [
            $this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

                $config = ComponentTemplates::class;

                $envAccessor = EnvAccessor::class;

                $container->replaceWithMock($config, $config, [

                    "getTemplateEntries" => [$this->componentEntry()]
                ]) // given. this, and template containing namespace that should parse to GeneratedUser
                ->replaceWithConcrete(
                    Database::class,
                    $this->replaceConstructorArguments(DatabaseMock::class, [ // refrain from using Models so the real thing doesn't get whacked

                        $envAccessor => $this->positiveDouble($envAccessor, ["getField" => "dummy"])
                    ], [

                        "componentInstallPath" => $this->getModelDirectory(),

                        "componentInstallNamespace" => __NAMESPACE__. "\\". $this->destinationFolder
                    ])
                );
            })
        ];
    }

    protected function getModelDirectory(): string
    {

        return implode(DIRECTORY_SEPARATOR, [

            __DIR__, $this->destinationFolder
        ]). DIRECTORY_SEPARATOR;
    }

    protected function componentEntry(): string
    {

        return EloquentComponentEntry::class;
    }

    public function test_correctly_writes_to_database_folder()
    {

        $this->assertInstalledComponent( // when

            $this->getCommandOptions(),
            true // for further assertions
        );

        // then
        $this->assertTrue(class_exists(GeneratedUser::class));

        $this->assertNotEmptyDirectory($this->getModelDirectory(), true);
    }

    protected function getCommandOptions(): array
    {

        return [

            InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
        ];
    }
}
