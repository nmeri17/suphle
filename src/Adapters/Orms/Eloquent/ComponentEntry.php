<?php

namespace Suphle\Adapters\Orms\Eloquent;

use Suphle\ComponentTemplates\BaseComponentEntry;

use Suphle\Contracts\Config\Database;

use Suphle\File\FolderCloner;

class ComponentEntry extends BaseComponentEntry
{
    public const EJECT_NAMESPACE = "database_namespace";

    public function __construct(
        protected readonly Database $databaseConfig,
        protected readonly FolderCloner $folderCloner
    ) {

        //
    }

    public function uniqueName(): string
    {

        return ""; // replaced by value defined on databaseConfig
    }

    protected function templatesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
    }

    /**
     * {@inheritdoc}
    */
    public function userLandMirror(): string
    {

        return $this->databaseConfig->componentInstallPath();
    }

    public function eject(): void
    {

        $content = $this->getContentReplacements(); // using a method for it to be overridable

        $this->folderCloner->setEntryReplacements($content, [], $content)
        ->transferFolder(
            $this->templatesLocation(),
            $this->userLandMirror()
        );
    }

    protected function getContentReplacements(): array
    {

        return [

            "_". self::EJECT_NAMESPACE => $this->databaseConfig->componentInstallNamespace()
        ];
    }
}
