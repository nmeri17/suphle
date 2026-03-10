<?php

namespace Suphle\WebSockets;

use Suphle\WebSockets\Contracts\Connection;

abstract class WebSocketGateway
{
    public function onConnect(Connection $conn): void
    {
    }

    public function onMessage(Connection $conn, string $raw): void
    {
    }

    public function onClose(Connection $conn): void
    {
    }
}
