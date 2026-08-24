<?php

namespace Suphle\Tests\Integration\Routing;

use Suphle\Routing\AttributeRouteScanner;

use Suphle\Testing\Utilities\ArrayAssertions;

class ModuleRequestRouterTest extends TestsRouter {

    use ArrayAssertions;

    public function test_extracts_route_information() {
        
        $routeScanner = $this->getContainer()->getClass(AttributeRouteScanner::class);
        $allRoutes = $routeScanner->scanAllModules();

        $this->assertIsArray($allRoutes);

        $this->assertArrayHasKeys($allRoutes[0], ['method', 'path', 'handler', 'middleware', 'canary_state', 'placeholders', 'coordinator']);
    }
    /**
     * @dataProvider pathsToHandler
    */
    public function test_route_matching(string $handler, string $requestPath)
    {

        $matchingRouteInfo = $this->findRouteInfo($requestPath);

        $this->assertNotNull($matchingRouteInfo);

        // var_dump($matchingRouteInfo->getPath(), $requestPath, 30);

        $this->assertTrue($matchingRouteInfo->handlerMatches($handler));
    }

    public function test_different_http_methods()
    {
        $testsUrl = "/test-method/placeholder";

        $this->put($testUrl, [])->assertOk();

        $this->delete($testUrl)->assertOk();
    }

    public function test_returns_null_for_non_existent_route()
    {
        $url = "/non-existent";// Given

        // When
        $this->get($url)

        // Then
        ->assertStatus(404);
    }
}
