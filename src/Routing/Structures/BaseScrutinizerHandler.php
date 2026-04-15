<?php

namespace Suphle\Routing\Structures;

abstract class BaseScrutinizerHandler
{
    abstract public function scrutinizeRequest(): void;
}
