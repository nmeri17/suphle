<?php
namespace Suphle\Tests\Unit\WebSockets;

use Suphle\WebSockets\WebSocketRouter;
use Suphle\Routing\AttributeRouteScanner;
use Suphle\Contracts\Config\Router as RouterConfig;
use Suphle\Testing\TestTypes\IsolatedComponentTest;
use Suphle\Tests\Integration\Generic\CommonBinds;

class WebSocketRouterTest extends IsolatedComponentTest
{
    use CommonBinds;

    public function test_registerRoutes_populates_from_scanner()
    {
        $scannerStub = $this->positiveDouble(AttributeRouteScanner::class, [ // given
            "scanModulesByPath" => ["/news" => "NewsGatewayClass"]
        ]);

        $configStub = $this->positiveDouble(RouterConfig::class, [
            "getWebSocketPath" => "Gateways"
        ]);

        // 3. Instantiate SUT via replaceConstructorArguments to satisfy the constructor
        // Signature: (Scanner $scanner, Router $routerConfig)
        $router = $this->replaceConstructorArguments(WebSocketRouter::class, [
            AttributeRouteScanner::class => $scannerStub,
            RouterConfig::class => $configStub
        ]);

        // when
        $router->registerRoutes();

        // then
        $this->assertEquals("ChatGateway", $router->getHandlerFor("/chat"));
    
        $this->assertNull($router->getHandlerFor("/non-existent"));
    }
}