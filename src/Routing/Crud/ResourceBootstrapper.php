<?php

namespace Suphle\Routing\Crud;

use Suphle\File\FolderCloner;

use Suphle\Contracts\Config\{Database, ModuleFiles};

use Suphle\Contracts\{Modules\DescriptorInterface, Database\OrmDialect, Presentation\HtmlParser};

class ResourceBootstrapper
{
    protected array $moduleList;

    public function __construct(
        protected readonly Database $databaseConfig,
        protected readonly DescriptorInterface $descriptor,
        protected readonly ModuleFiles $fileConfig,
        protected readonly OrmDialect $ormDialect,
        protected readonly HtmlParser $htmlParser,
        protected readonly FolderCloner $folderCloner
    ) {

        //
    }

    public function outputResourceTemplates(string $resourceName, ?bool $isApi): bool
    {

        $contentsReplacement = $this->getContentReplacements($resourceName);

        // Add a specific replacement for the RoutePrefix attribute logic
        $contentsReplacement["_mirror_config"] = $isApi 
        ? 'mirrorPrefix: "api/v1"' // If it's an API resource, we definitely want a prefix
        : '';

        $this->folderCloner->setEntryReplacements(
            $contentsReplacement,
            $contentsReplacement,
            $contentsReplacement
        );

        $genericTransfer = $this->folderCloner->transferFolder(
            __DIR__. DIRECTORY_SEPARATOR. "BootstrapTemplates",
            $this->fileConfig->activeModulePath()
        );

        $databaseTransfer = $this->folderCloner->transferFolder(
            $this->ormDialect->crudFilesLocation(),
            $this->databaseConfig->componentInstallPath()
        );

        if ($isApi) {
            $viewTransfer = true;
        } else {
            $viewTransfer = $this->folderCloner->transferFolder(
                $this->htmlParser->crudFilesLocation(),
                $this->fileConfig->defaultViewPath(). $resourceName
            );
        }

        return $genericTransfer && $databaseTransfer && $viewTransfer;
    }

    protected function getContentReplacements(string $resourceName): array
    {

        return [

            "_module_name" => @end(explode(
                "\\",
                $this->descriptor->exportsImplements()
            )),

            "_database_namespace" => $this->databaseConfig->componentInstallNamespace(),

            "_resource_name" => $resourceName,

            "_resource_route" => strtoupper($resourceName),

            "_modules_shell" => $this->fileConfig->modulesNamespace()
        ];
    }
}
