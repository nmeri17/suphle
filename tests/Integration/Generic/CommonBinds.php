<?php

namespace Suphle\Tests\Integration\Generic;

use Suphle\Contracts\Config\{Router, ModuleFiles};

use Suphle\Config\AscendingHierarchy;

use Suphle\File\FileSystemReader;

use Suphle\Hydration\Container;

use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

trait CommonBinds
{
    protected function simpleBinds(): array
    {

        return array_merge(parent::simpleBinds(), [

            Router::class => RouterMock::class
        ]);
    }

    protected function fileConfigModuleName(): string
    {

        return "ModuleOne";
    }

    protected function concreteBinds(): array
    {

        $container = $this->getContainer();

        $systemReader = $container->getClass(FileSystemReader::class);

        $anchorPath = $systemReader->pathFromLevels(
            __DIR__,
            "Mocks/Modules/". $this->fileConfigModuleName() . "/Config", // "config" so that back tracking by levels will land us at module root. Can be any folder there
            2
        );

        return array_merge(parent::concreteBinds(), [

            ModuleFiles::class => new AscendingHierarchy(
                $anchorPath,
                $this->getNamespace($container),
                $systemReader
            )
        ]);
    }

    protected function getNamespace(Container $container): string
    {

        return "\Suphle\Tests\Mocks\Modules\\". $this->fileConfigModuleName();
    }
}
