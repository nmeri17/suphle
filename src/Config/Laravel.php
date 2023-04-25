<?php

namespace Suphle\Config;

use Suphle\Contracts\Config\Laravel as LaravelConfigContract;

class Laravel implements LaravelConfigContract
{
    /**
     * {@inheritdoc}
    */
    public function configBridge(): array
    {

        return [];
    }

    /**
     * {@inheritdoc}
    */
    public function registersRoutes(): bool
    {

        return false;
    }
}
