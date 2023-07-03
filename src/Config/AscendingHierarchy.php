<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\ModuleFiles;

use Suphle\File\FileSystemReader;

/**
 * Returned paths have trailing slashes
*/
class AscendingHierarchy implements ModuleFiles
{
    public function __construct(protected readonly string $descriptorPath, protected readonly string $descriptorNamespace, protected readonly FileSystemReader $systemReader)
    {

        //
    }

    /**
     * {@inheritdoc}
    */
    public function getRootPath(): string
    {

        return $this->systemReader->pathFromLevels(
            $this->descriptorPath,
            "",
            3 // moduleContents/allModules/projectRoot
        );
    }

    public function modulesNamespace(): string
    {

        return "AllModules";
    }

    /**
     * {@inheritdoc}
    */
    public function activeModulePath(): string
    {

        return $this->systemReader->pathFromLevels(
            $this->descriptorPath,
            "",
            1
        );
    }

    /**
     * {@inheritdoc}
    */
    public function defaultViewPath(): string
    {

        return $this->activeModulePath(). "Markup" . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
    */
    public function getImagePath(): string
    {

        return $this->getRootPath(). "public/Images" . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
    */
    public function componentsPath(): string
    {

        return $this->activeModulePath(). "InstalledComponents" . DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
    */
    public function componentsNamespace(): string
    {

        $segments = explode("\\", $this->descriptorNamespace);

        array_pop($segments);

        return implode("\\", $segments). "\InstalledComponents";
    }
}
