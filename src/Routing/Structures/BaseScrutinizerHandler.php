<?php

namespace Suphle\Routing\Structures;

abstract class BaseScrutinizerHandler
{
    use ReceivesMetaFunnel;

    abstract public function scrutinizeRequest(): void;
}
