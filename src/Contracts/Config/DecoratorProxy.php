<?php

namespace Suphle\Contracts\Config;

interface DecoratorProxy extends ConfigMarker
{
    public function getConfigClient(): object;
}
