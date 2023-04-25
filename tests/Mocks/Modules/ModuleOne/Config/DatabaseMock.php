<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

use Suphle\Config\PDOMysqlKeys;

class DatabaseMock extends PDOMysqlKeys
{
    /**
     * {@inheritdoc}
    */
    public function componentInstallPath(): string
    {

        return $this->fileConfig->getRootPath().

        "Models\Eloquent" . DIRECTORY_SEPARATOR;
    }

    public function componentInstallNamespace(): string
    {

        return "Suphle\Tests\Mocks\Models\Eloquent";
    }
}
