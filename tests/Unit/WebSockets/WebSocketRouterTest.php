<?php

namespace Suphle\Tests\Unit\WebSockets;

use PHPUnit\Framework\TestCase;
use Suphle\WebSockets\WebSocketRouter;
use Suphle\WebSockets\WebSocketGateway;
use Suphle\Hydration\Container;

class WebSocketRouterTest extends TestCase
{
    public function test_can_register_and_retrieve_routes()
    {
        $container = $this->createMock(Container::class);
        $gatewayMock = $this->createMock(WebSocketGateway::class);
        
        $container->method('getClass')
            ->willReturn($gatewayMock);

        $router = new WebSocketRouter($container);
        
        // Simulating the result of scanModuleForGateways
        $router->addRoute('/chat', get_class($gatewayMock));
        
        $handler = $router->getHandlerFor('/chat');
        
        $this->assertInstanceOf(WebSocketGateway::class, $handler);
        $this->assertNull($router->getHandlerFor('/missing'));
    }
}
