<?php
namespace Suphle\Auth;

use Suphle\File\{FolderTemplatePlaceholders, FolderCloner};

use Suphle\Contracts\Config\{Database, ModuleFiles, Router};

use Suphle\Contracts\Modules\DescriptorInterface;

use Suphle\ComponentTemplates\BaseComponentEntry;

class ComponentEntry extends BaseComponentEntry {

    use FolderTemplatePlaceholders;

    public function __construct(
        protected readonly Database $databaseConfig,
        protected readonly DescriptorInterface $descriptor,
        protected readonly ModuleFiles $fileConfig,
        protected readonly FolderCloner $folderCloner,
        protected readonly Router $routerConfig
    ) {}

    /**
     * {@inheritdoc}
    */
    public function uniqueName(): string {

        return "SuphleIdentity";
    }

    protected function templatesLocation(): string
    {

        return __DIR__ . DIRECTORY_SEPARATOR . "ComponentTemplates";
    }

    public function eject(): void {

        $content = $this->getContentReplacements();

        $source = $this->templatesLocation();

        $this->folderCloner->setEntryReplacements($content, [], $content);

        foreach ([
            "Coordinators" => $this->routerConfig->getCoordinatorPath(),

            "Database" => $this->databaseConfig->componentInstallPath(),

            "Tests" => implode(DIRECTORY_SEPARATOR, [
                $this->fileConfig->activeModulePath(), "Tests", "Auth"
            ])
        ] as $folder => $destination)
            
            $this->folderCloner->transferFolder($source. DIRECTORY_SEPARATOR. $folder, $destination);

        $this->folderCloner->transferFolder($source, $this->userLandMirror());
    }

    protected function getContentReplacements(string $resourceName): array {

        return array_merge($this->moduleResourceValues($resourceName), [

            "_database_namespace" => $this->databaseConfig->componentInstallNamespace(),
        ]);
    }
}
