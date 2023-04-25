<?php

namespace Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders;

use Illuminate\Support\ServiceProvider;

use Suphle\Tests\Mocks\Modules\ModuleOne\InstalledComponents\SuphleLaravelTemplates\ServiceProviders\Exports\ConfigInternal;

class ConfigInternalProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton(ConfigInternal::class, fn ($app) => new ConfigInternal());
    }
}
