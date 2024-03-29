<?php

namespace Suphle\Contracts\Config;

interface ModuleFiles extends ConfigMarker
{
    /**
     * @return Absolute path, with trailing slash
    */
    public function getRootPath(): string;

    /**
     * Without trailing slash
    */
    public function modulesNamespace(): string;

    /**
     * @return Absolute path, with trailing slash
    */
    public function activeModulePath(): string;

    /**
     * @return Absolute path, with trailing slash
    */
    public function defaultViewPath(): string;

    /**
     * @return Absolute path, with trailing slash
    */
    public function getImagePath(): string;

    /**
     * @return Absolute path, with trailing slash
    */
    public function componentsPath(): string;

    /**
     * For use when ejecting classes
    */
    public function componentsNamespace(): string;
}
