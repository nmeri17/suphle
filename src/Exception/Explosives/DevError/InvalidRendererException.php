<?php

namespace Suphle\Exception\Explosives\DevError;

use Suphle\Exception\Explosives\DevError\ExplosiveError;

class InvalidRendererException extends ExplosiveError
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
} 