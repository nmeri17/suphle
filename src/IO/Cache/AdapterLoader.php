<?php

namespace Suphle\IO\Cache;

use Suphle\Contracts\IO\EnvAccessor;

use Suphle\Hydration\BaseInterfaceLoader;

use Suphle\Adapters\Cache\PredisAdapter;

class AdapterLoader extends BaseInterfaceLoader
{
    public function afterBind($initialized): void
    {

        $initialized->setupClient();
    }

    public function concreteName(): string
    {

        return PredisAdapter::class;
    }
}
