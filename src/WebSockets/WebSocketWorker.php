<?php
namespace Suphle\WebSockets;

use Suphle\Hydration\Container;

use Spiral\RoadRunner\Worker;

use RoadRunner\Centrifugo\{CentrifugoWorker, Request, Payload, Request\RequestFactory};

class WebSocketWorker {

    public function __construct (protected readonly Container $container) {}

    protected function processWebSocketTasks(): void
    {
        $worker = Worker::create();
        $requestFactory = new RequestFactory($worker);
        $centrifugoWorker = new CentrifugoWorker($worker, $requestFactory);

        $router = $this->container->getClass(WebSocketRouter::class);

        while ($request = $centrifugoWorker->waitRequest()) {
            if ($request instanceof Request\Invalid) continue;

            // Centrifugo doesn't have a 'path' in the URL sense, 
            // it uses 'method' for RPC or channels for Subscribe.
            $path = $this->derivePathFromCentrifugo($request);
            $gatewayClass = $router->getHandlerFor($path);

            if ($gatewayClass) {
                $gateway = $this->container->getClass($gatewayClass);
                $connection = new RoadRunnerConnection($request);

                $this->handleCentrifugoEvent($gateway, $connection, $request);
            }
        }
    }

    private function derivePathFromCentrifugo(Request\RequestInterface $request): string
    {
        if ($request instanceof Request\RPC) return $request->method;
        if ($request instanceof Request\Subscribe) return $request->channel;
        return '/'; // Default/Connect
    }

    private function handleCentrifugoEvent($gateway, $connection, $request): void
    {
        try {
            if ($request instanceof Request\Connect) {
                $gateway->onConnect($connection);
                $request->respond(new Payload\ConnectResponse(user: $connection->getId()));
            }
            elseif ($request instanceof Request\RPC || $request instanceof Request\Publish) {
                // This is the closest equivalent to "onMessage"
                $gateway->onMessage($connection, json_encode($request->data));
                $request->respond(new Payload\RPCResponse(data: ["status" => "ok"]));
            }
            elseif ($request instanceof Request\Subscribe) {
                $request->respond(new Payload\SubscribeResponse());
            }
        } catch (\Throwable $e) {
            $request->error($e->getCode(), $e->getMessage());
        }
    }
}