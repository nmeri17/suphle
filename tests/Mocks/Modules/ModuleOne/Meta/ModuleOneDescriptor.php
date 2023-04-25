<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Meta;

use Suphle\Modules\ModuleDescriptor;

use Suphle\Contracts\Config\ModuleFiles;

use Suphle\File\FileSystemReader;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\FilesMock;

use Suphle\Tests\Mocks\Interactions\ModuleOne;

class ModuleOneDescriptor extends ModuleDescriptor
{
    public function exportsImplements(): string
    {

        return ModuleOne::class;
    }

    /**
     * {@inheritdoc}
    */
    public function interfaceCollection(): string
    {

        return CustomInterfaceCollection::class;
    }

    public function globalConcretes(): array
    {

        return array_merge(parent::globalConcretes(), [

            ModuleFiles::class => new FilesMock(
                __DIR__,
                __NAMESPACE__,
                $this->container->getClass(FileSystemReader::class)
            )
        ]);
    }
}
