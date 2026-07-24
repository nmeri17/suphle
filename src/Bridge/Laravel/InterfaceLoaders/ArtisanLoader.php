<?php

namespace Suphle\Bridge\Laravel\InterfaceLoaders;

use Suphle\Hydration\BaseInterfaceLoader;

use Suphle\Contracts\Bridge\LaravelContainer;

use Suphle\Bridge\Laravel\ArtisanConcrete;

use Illuminate\{Console\Application, Events\Dispatcher};

class ArtisanLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly LaravelContainer $laravelContainer
    ) { }

    public function bindArguments(): array
    {

        $this->laravelContainer->loadDeferredProviders(); // it's important that providers are booted before our concrete is being instantiated, since concrete will expect commands to have already been injected into console, which only happens during booting

        return [
            LaravelContainer::class => $this->laravelContainer,

            Dispatcher::class => $this->laravelContainer->make(Dispatcher::class)
        ];
    }

    public function afterBind ($initialized):void {

        $initialized->setContainerCommandLoader(); // this releases all the sub/internal commands. laravel doesn't log them into the symfony registry cuz they want to handle it themselves. without giving symfony this loader, it never delegates to laravel when it receives the low-level commands eg db:wipe
    }

    public function concreteName(): string
    {

        return ArtisanConcrete::class;
    }
}
