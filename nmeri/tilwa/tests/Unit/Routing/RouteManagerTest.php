<?php

	namespace Tilwa\Tests\Unit\Routing;

	use Tilwa\Testing\IsolatedComponentTest;

	use Tilwa\Routing\RouteManager;

	class RouteManagerTest extends IsolatedComponentTest {

		public function test_route_compare_hyphen () {

			$this->setHttpParams("/segment-segment/5");

			$router = $this->container->getClass(RouteManager::class);

			$this->assertFalse($router->routeCompare("/SEGMENT/id/", "get"));

			$this->assertTrue($router->routeCompare("/SEGMENT-SEGMENT/id/", "get"));
		}

		/**
	     * @dataProvider compareOptionalData
	     */
		public function test_route_compare_optional (string $path) {

			$this->setHttpParams($path);

			$router = $this->container->getClass(RouteManager::class);

			$this->assertTrue($router->routeCompare("SEGMENT/id/SEGMENT/(id/)?", "get"));
		}

		public function compareOptionalData ():array {

			return [
				[ "/segment/5/segment/5/"], // without slash should be made to work too
				[ "/segment/5/segment"]
			];
		}

		/**
	     * @dataProvider regexFormData
	     */
		public function test_regex_form (string $rawForm, string $regexVersion) {

			$router = $this->container->getClass(RouteManager::class);

			$this->assertSame($router->regexForm($rawForm), $regexVersion);
		}

		public function regexFormData ():array {

			return [
				["SEGMENT_id_SEGMENT_idO", "SEGMENT/id/SEGMENT/(id/)?"],
				["SEGMENT_id", "SEGMENT/id/"]
			];
		}
	}
?>