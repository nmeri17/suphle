<?php

	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Routing\RouteManager;

	class RouteManagerTest extends BaseTest {

		public function test_route_compare (/* string $handler, string $requestPath*/) {

			$this->setHttpParams("/segment-segment/5");

			$router = $this->container->getClass(RouteManager::class);

			$this->assertFalse($router->routeCompare("/SEGMENT/id/", "get"));

			$this->assertTrue($router->routeCompare("/SEGMENT-SEGMENT/id/", "get"));
		}
	}
?>