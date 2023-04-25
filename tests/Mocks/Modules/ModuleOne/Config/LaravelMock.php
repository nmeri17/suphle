<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\Config;

use Suphle\Config\Laravel as ParentConfig;

use Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ConfigLinks\{AppConfig, NestedConfig};

class LaravelMock extends ParentConfig
{
    /**
     * {@inheritdoc}
    */
    public function configBridge(): array
    {

        return [

            "app" => AppConfig::class,

            "nested" => NestedConfig::class
        ];
    }

    /**
     * {@inheritdoc}
    */
    public function registersRoutes(): bool
    {

        return true;
    }
}
