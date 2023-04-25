<?php

namespace Suphle\Contracts\IO;

/**
 * While config and env are for deriving dynamic values, env is intended for reading infra-level values that hold different entries in-between environments (e.g. keys for prod and dev). Other types of setting are likely application-level, and should be computed from a config class tending to the component
*/
interface EnvAccessor
{
    public function getField(string $name, $defaultValue = null);

    //public function setField (string $name, $value):void;
}
