<?php

namespace Suphle\Bridge\Laravel\InterfaceLoaders;

use Suphle\Hydration\BaseInterfaceLoader;

use Suphle\Bridge\Laravel\{LaravelAppConcrete, ComponentEntry};

use Suphle\Contracts\Bridge\LaravelContainer;

use Illuminate\Support\Facades\Facade;

use Illuminate\Foundation\Bootstrap\LoadConfiguration;

class LaravelAppLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly ComponentEntry $componentEntry,
        protected readonly LoadConfiguration $configLoader
    ) {

        //
    }

    public function bindArguments(): array
    {

        return [

            "basePath" => $this->getBasePath()
        ];
    }

    public function concreteName(): string
    {

        return LaravelAppConcrete::class;
    }

    public function afterBind($initialized): void
    {

        $this->injectBindings($initialized); // required for below call

        $initialized->overrideAppHelper();

        $this->configLoader->bootstrap($initialized);

        $initialized->runContainerBootstrappers();
    }

    protected function injectBindings(LaravelContainer $laravelContainer): void
    {

        Facade::setFacadeApplication($laravelContainer);

        $laravelContainer->registerConcreteBindings($laravelContainer->concreteBinds());

        $laravelContainer->registerSimpleBindings($laravelContainer->simpleBinds());
    }

    protected function getBasePath(): string
    {

        return $this->componentEntry->userLandMirror();
    }
}
