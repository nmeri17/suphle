<?php

namespace Suphle\Tests\Integration\ComponentTemplates;

use Suphle\Contracts\Config\ComponentTemplates;

use Suphle\Hydration\Container;

use Suphle\ComponentTemplates\Commands\InstallComponentCommand;

use Suphle\Adapters\Orms\Eloquent\ComponentEntry as EloquentComponentEntry;

use Suphle\Testing\{TestTypes\CommandLineTest, Proxies\WriteOnlyContainer};

use Suphle\Tests\Mocks\Modules\ModuleOne\Meta\ModuleOneDescriptor;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

use Symfony\Component\Console\{Command\Command, Tester\CommandTester};

class GenericComponentTest extends CommandLineTest
{
    protected array $treasuredComponents = [

        EloquentComponentEntry::class
    ];

    protected ?array $componentList = null;

    protected function getModules(): array
    {

        return [new ModuleOneDescriptor(new Container())];
    }

    public function test_can_install_all_components()
    {

        $config = ComponentTemplates::class;

        $this->massProvide([ // given

            $config => $this->positiveDouble($config, [

                "getTemplateEntries" => $this->getComponentList()
            ])
        ]);

        $command = $this->consoleRunner->findHandler(
            InstallComponentCommand::commandSignature()
        );

        $result = (new CommandTester($command))

        ->execute([ // when

            InstallComponentCommand::HYDRATOR_MODULE_OPTION => ModuleOne::class
        ]);

        $this->assertSame(Command::SUCCESS, $result); // sanity check

        $this->assertInstalledAll();
    }

    protected function getComponentList(): array
    {

        if (!is_null($this->componentList)) {

            return $this->componentList;
        }

        $defaultList = $this->getContainer()->getClass(ComponentTemplates::class)

        ->getTemplateEntries();

        foreach ($this->treasuredComponents as $entryName) {

            $index = array_search($entryName, $defaultList);

            if ($index !== false) {
                unset($defaultList[$index]);
            }
        }

        return $this->componentList = $defaultList;
    }

    protected function assertInstalledAll(): void
    {

        $this->assertSame( // then

            count($this->getComponentList()),
            count($this->getInstalledComponents())
        );
    }

    protected function getInstalledComponents(): array
    {

        $container = $this->getContainer();

        $componentInstances = array_map(function ($entry) use ($container) {

            return $container->getClass($entry);
        }, $this->getComponentList());

        return array_filter($componentInstances, function ($entry) {

            return $entry->hasBeenEjected();
        });
    }
}
