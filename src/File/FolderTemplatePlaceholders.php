<?php
namespace Suphle\File;

use Exception;

trait FolderTemplatePlaceholders {

    protected function moduleResourceValues(string $resourceName): array {

        return [

            "_module_name" => @end(explode(
                "\\",
                $this->descriptor->exportsImplements()
            )),

            "_resource_name" => $resourceName,

            "_resource_route" => strtolower($resourceName),

            "_modules_shell" => $this->fileConfig->modulesNamespace()
        ];
    }
}