<?php

namespace Suphle\Bridge\Laravel;

use Suphle\Contracts\{Config\Laravel, Bridge\LaravelContainer};

use Suphle\Services\Decorators\BindsAsSingleton;

use Suphle\Bridge\Laravel\DefaultExceptionHandler;

use Illuminate\Http\Request as LaravelRequest;

use Illuminate\Foundation\Application;

use Illuminate\Foundation\Bootstrap\{RegisterFacades, RegisterProviders, BootProviders};

use Illuminate\Contracts\Debug\ExceptionHandler;

use Illuminate\Config\Repository as ConfigRepo;

use ReflectionClass;

#[BindsAsSingleton(LaravelContainer::class)]
class LaravelAppConcrete extends Application implements LaravelContainer
{
    protected const KERNEL_BOOTSTRAPPERS = [

        RegisterFacades::class, RegisterProviders::class,

        BootProviders::class
    ];

    private static bool $hasSetApp = false;

    public function __construct(
        protected readonly ConfigRepo $configRepo,
        string $basePath
    ) {

        parent::__construct($basePath);
    }

    public function protectRefreshPurge(): bool
    {

        return true;
    }

    public function concreteBinds(): array
    {

        return [
            "app" => $this,

            "config" => $this->configRepo,
        ];
    }

    public function simpleBinds(): array
    {

        return [

            ExceptionHandler::class => DefaultExceptionHandler::class
        ];
    }

    public function registerConcreteBindings(array $bindings): void
    {

        foreach ($bindings as $alias => $concrete) {

            $this->instance($alias, $concrete);
        }
    }

    public function registerSimpleBindings(array $bindings): void
    {

        foreach ($bindings as $alias => $concrete) {

            $this->singleton($alias, $concrete);
        }
    }

    public function runContainerBootstrappers(): void
    {

        foreach (self::KERNEL_BOOTSTRAPPERS as $bootstrapper) {

            (new $bootstrapper())->bootstrap($this);
        }
    }

    public function overrideAppHelper(): void
    {

        if (self::$hasSetApp) {
            return;
        }

        function app()
        {
            return $this;
        }

        self::$hasSetApp = true;
    }
}
