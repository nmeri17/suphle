<?php

namespace Suphle\ComponentTemplates;

use Suphle\Contracts\Config\ModuleFiles;

use Suphle\File\FileSystemReader;

abstract class BaseComponentEntry
{
    protected array $inputArguments = [];

    public function __construct(
        protected readonly ModuleFiles $fileConfig,
        protected readonly FileSystemReader $fileSystemReader
    ) {

        //
    }

    public function hasBeenEjected(): bool
    {

        return file_exists($this->userLandMirror());
    }

    /**
     * Destination to deposit template files
     *
     * @return With trailing slash
    */
    public function userLandMirror(): string
    {

        return $this->fileConfig->componentsPath() . $this->uniqueName() . DIRECTORY_SEPARATOR;
    }

    /**
     * Can use the pattern, {Vendor}_{Component}, to guarantee uniqueness
    */
    abstract public function uniqueName(): string;

    /**
     * @return Absolute path
    */
    abstract protected function templatesLocation(): string;

    public function eject(): void
    {

        $this->fileSystemReader->deepCopy(
            $this->templatesLocation(),
            $this->userLandMirror()
        );
    }

    /**
     * @param {argumentList}: [foo=>value,uju=>bar]
    */
    public function setInputArguments(array $argumentList): void
    {

        $this->inputArguments = $argumentList;
    }
}
