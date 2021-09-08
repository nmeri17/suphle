<?php

	namespace Tilwa\Tests\Integration\App;

	use Tilwa\Testing\BaseTest;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Response\Format\{AbstractRenderer, Json};

	class RouteManagerTest extends BaseTest {

		/**
	     * @dataProvider pathsToHandler
	     */
		public function test_route_matching ( string $handler, string $requestPath) {

			$this->setHttpParams($requestPath); // this should be the first line in all the tests involving pulling path from requestDetails?

			$router = $this->container->getClass(RouteManager::class);

			$router->findRenderer();

			$matchingRenderer = $router->getActiveRenderer();

			$this->assertNotNull($matchingRenderer);
			
			var_dump($matchingRenderer->getPath(), $requestPath, 30);
			$this->assertSame($matchingRenderer->getPath(), $requestPath);

			$this->assertSame($matchingRenderer->getHandler(), $handler);
		}

		public function pathsToHandler ():array {

			return [
				[ "plainSegment", "/segment"],
				[ "simplePair", "/segment/5"],
				[ "hyphenatedSegments", "/segment-segment/5"],
				[ "underscoredSegments", "/segment_segment/5"],
				[ "optionalPlaceholder", "/segment/5/segment/5"],
				[ "optionalPlaceholder", "/segment/5/segment"]
			];
		}
	}
?>