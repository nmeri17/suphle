<?php

namespace Suphle\IO\Env;

use Suphle\Contracts\{IO\EnvAccessor, Config\ModuleFiles};

use Dotenv\Dotenv;

/**
 * {@inheritdoc}
*/
abstract class AbstractEnvReader implements EnvAccessor
{
    protected Dotenv $client;

    public function __construct(protected readonly ModuleFiles $fileConfig)
    {

        $this->setClient();

        $this->client->safeLoad(); // file is not expected to exist in production, as variables will be set by deployment vendor

        $this->validateFields();
    }

    public function getField(string $name, $defaultValue = null)
    {

        $readValue = $_ENV[$name] ?? getenv($name); // most runners write to $_ENV except Symfony\Process (used in RoadRunner tests) that writes to getenv

        if ($readValue === false) {
            return $defaultValue;
        }

        return $readValue;
    }

    /*public function setField (string $name, $value):void {

        $_ENV[$name] = $value;
    }*/

    /**
     * Make use of [client]
    */
    abstract protected function validateFields(): void;

    protected function setClient(): void
    {

        $path = $this->fileConfig->activeModulePath();

        $this->client = Dotenv::createImmutable($path); // don't programmatically overwrite env values
    }
}
