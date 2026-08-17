<?php

namespace Suphle\Adapters\Orms\Eloquent;

use Suphle\Hydration\{BaseInterfaceLoader, Container};

use Suphle\Contracts\Config\{Auth, Database};

use Suphle\Contracts\{Bridge\LaravelContainer, Database\OrmDialect, Auth\AuthStorage};

use Suphle\Adapters\Orms\Eloquent\Models\BaseModel;

use Illuminate\Events\Dispatcher;

class OrmLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly Auth $authContract,
        protected readonly AuthStorage $authStorage,
        protected readonly LaravelContainer $laravelContainer,
        protected readonly Container $container,
        protected readonly Database $databaseConfig
    ) {

        //
    }

    public function afterBind($initialized): void
    {

        $this->laravelContainer->registerConcreteBindings($this->databaseBindings($initialized)); // implicitly sets connection

        $client = $initialized->getNativeClient();

        $client->setEventDispatcher($this->laravelContainer->make(Dispatcher::class));

        $client->bootEloquent(); // in addition to using the above to register observers below, this does the all important job of Model::setConnectionResolver for us

        $this->injectHydrator($initialized); // just before giving this to the observers

        $initialized->registerObservers(
            $this->authContract->getModelObservers(),
            $this->authStorage
        );

        BaseModel::shouldBeStrict();

        $this->laravelContainer->useDatabasePath($this->databaseConfig->componentInstallPath()); // used by cli runner eg migrate, to read elsewhere from the component installation path, enabling eg migrate commands to point to our global folder

        /*this bit is specific to laravel 9 and below. if you ever upgrade to 12, get rid of it as it serves to silence the error thrown in esd:263 where we have 'type' => $this->mapDbType($builder->getColumnType($table, $col)). replace with $builder->getColumnType($table, $col);*/
        $platform = $initialized->getConnection()->getDoctrineSchemaManager()
        ->getDatabasePlatform();

        $platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function concreteName(): string
    {

        return OrmBridge::class;
    }

    protected function databaseBindings(OrmDialect $initialized): array
    {

        return [

            "db.connection" => $initialized->getConnection(),

            "db" => $initialized->getNativeClient()->getDatabaseManager()
        ];
    }

    protected function injectHydrator(OrmDialect $initialized): void
    {

        $authStorage = $this->authStorage;

        $authStorage->setHydrator($initialized->getUserHydrator());

        $this->container->whenTypeAny()->needsAny([

            AuthStorage::class => $authStorage
        ]);
    }
}
