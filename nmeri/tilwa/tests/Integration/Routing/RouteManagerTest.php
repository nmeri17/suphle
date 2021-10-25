<?php
	namespace Tilwa\Tests\Integration\Routing;

	class RouteManagerTest extends BaseRouterTest {

		/**
	     * @dataProvider pathsToHandler
	     */
		public function test_route_matching ( string $handler, string $requestPath) {

			$matchingRenderer = $this->fakeRequest($requestPath);

			$this->assertNotNull($matchingRenderer);
			
			// var_dump($matchingRenderer->getPath(), $requestPath, 30);

			$this->assertSame($matchingRenderer->getHandler(), $handler);
		}

		public function pathsToHandler ():array {

			return [
				[ "plainSegment", "/segment"],
				[ "plainSegment", "/segment/"],

				[ "simplePair", "/segment/5"],
				[ "simplePair", "/segment/5/"],

				[ "hyphenatedSegments", "/segment-segment/5"],
				[ "hyphenatedSegments", "/segment-segment/5/"],

				[ "underscoredSegments", "/segment_segment/5"],

				[ "optionalPlaceholder", "/segment/5/segment/5"],
				[ "optionalPlaceholder", "/segment/5/segment"]
			];
		}
	}
?>