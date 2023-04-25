<?php

namespace Suphle\Contracts\Server;

interface DependencyFileHandler
{
    public function evaluateClass(string $className): void;

    public function setRunArguments(array $argumentList): void;
}
