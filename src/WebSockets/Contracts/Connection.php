<?php

namespace Suphle\WebSockets\Contracts;

interface Connection
{
    public function getId(): string;
    
    public function query(string $key, $default = null): mixed;
    
    public function send(string $message): void;
}
