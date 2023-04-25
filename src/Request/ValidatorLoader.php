<?php

namespace Suphle\Request;

use Suphle\Hydration\BaseInterfaceLoader;

use Suphle\Adapters\Validators\LaravelValidator;

use Suphle\Contracts\{ Config\AuthContract, Bridge\LaravelContainer, Database\OrmDialect};

use Illuminate\Database\Capsule\Manager as Capsule;

use Illuminate\Validation\{Factory as ValidationFactory, DatabasePresenceVerifier};

use Illuminate\Translation\{FileLoader, Translator};

use Illuminate\Filesystem\Filesystem;

class ValidatorLoader extends BaseInterfaceLoader
{
    public function __construct(
        protected readonly LaravelContainer $laravelContainer,
        protected readonly OrmDialect $ormDialect
    ) {

        //
    }

    public function bindArguments(): array
    {

        $client = $this->getValidationClient();

        $databaseManager = $this->ormDialect->getNativeClient()

        ->getDatabaseManager();

        $client->setPresenceVerifier(new DatabasePresenceVerifier($databaseManager));

        return [

            ValidationFactory::class => $client
        ];
    }

    private function getValidationClient(): ValidationFactory
    {

        $translator = new Translator(
            new FileLoader(new Filesystem(), "lang"),
            "en"
        );

        return new ValidationFactory($translator, $this->laravelContainer);
    }

    public function concreteName(): string
    {

        return LaravelValidator::class;
    }
}
