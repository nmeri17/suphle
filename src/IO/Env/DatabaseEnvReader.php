<?php

namespace Suphle\IO\Env;

use Suphle\IO\Env\AbstractEnvLoader;

class DatabaseEnvReader extends AbstractEnvReader
{
    protected function validateFields(): void
    {

        $this->client->required([

            "DATABASE_NAME", "DATABASE_USER", "DATABASE_PASS"
        ]);
    }
}
