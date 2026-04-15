<?php
namespace Suphle\WebSockets;

use Suphle\WebSockets\Contracts\Connection;
use RoadRunner\Centrifugo\Request\RequestInterface;

class RoadRunnerConnection implements Connection
{
    public function __construct(
        private readonly RequestInterface $request
    ) {}

    public function getId(): string
    {
        // Centrifugo requests contain client/connection IDs
        return $this->request->client;
    }

    public function query(string $key, $default = null): mixed
    {
        // Centrifugo sends metadata/headers in the request
        return $this->request->metadata[$key] ?? $default;
    }

    public function send(string $message): void
    {
        /** * NOTE: Centrifugo is a proxy. To "send" back, you either:
         * 1. Respond to the current request (RPC/Subscribe)
         * 2. Use the Centrifugo RPC API to broadcast (out of scope for this wrapper)
         */
    }
}