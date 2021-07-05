<?php

	namespace Tilwa\Tests\Integration\App;

	use Tilwa\App\{ModuleInitializer, ModuleDescriptor};

	use Tilwa\Testing\BaseTest;

	use Tilwa\Routing\{RouteManager, BaseCollection};

	use Tilwa\Response\Format\{AbstractRenderer, Markup};

	use Prophecy\Prophecy\ObjectProphecy;

	class ModuleInitializerTest extends BaseTest {

		private $routeMatcher;

		protected function setUp ():void {

			parent::setUp();

			$this->routeMatcher = (new ModuleInitializer($this->getDescriptor()->reveal()));
		}

		/**
	     * @dataProvider getCaseData
	     */
		public function test_assign_route (string $methodName, AbstractRenderer $renderer, string $requestPath) {

			/*- Needs a route collection passed to route manager
			- That guy contains a renderer we know/tag
			- Afterwards, we assert that it foundRoute and renderer matches our guy*/

			$this->routeMatcher->initialize();

			$router = $this->injectCollectionToRouter($methodName, $renderer);

			$_GET["tilwa_path"] = $requestPath;

			$_SERVER["REQUEST_METHOD"] = "get";

			$this->routeMatcher->assignRoute();
			
			$this->assertTrue($this->routeMatcher->didFindRoute());

			$this->assertEquals($router->findRenderer(), $renderer);
		}

		private function getDescriptor():ObjectProphecy {

			$descriptor = $this->prophet->prophesize(ModuleDescriptor::class);

			$descriptor->getContainer()->willReturn($this->container);

			$descriptor->getConfigs()->willReturn([]);

			return $descriptor;
		}

		# @return Router double
		private function injectCollectionToRouter (string $methodName, AbstractRenderer $renderer) {

			$router = $this->prophet->prophesize(RouteManager::class);

			$collection = $this->prophet->prophesize("collection");

			$collection->willExtend(BaseCollection::class)

			->$methodName()->will(function ($args) use ($methodName, $renderer) {

				$method = "_$methodName";

				$this->$method($renderer); // for nested collections, exchange this callback for one containing prefix, canary etc
			});

			$router->entryRouteMap()->willReturn([$collection->reveal()]);

			$routerInstance = $router->reveal();

			$this->container->whenTypeAny()->needsAny([

				RouteManager::class => $routerInstance
			]);

			return $routerInstance;
		}

		public function getCaseData ():array {

			return [
				["SEGMENT", new Markup("", ""), "/segment"],
				["SEGMENT_id", new Markup("", ""), "/segment/5"],
				["SEGMENT__SEGMENTh_id", new Markup("", ""), "/segment-segment/5"],
				["SEGMENT_SEGMENTh_id", new Markup("", ""), "/segment_segment/5"],
				["SEGMENT_id_SEGMENT_id0", new Markup("", ""), "/segment/5/segment/5"],
				["SEGMENT_id_SEGMENT_id0", new Markup("", ""), "/segment/5/segment"]
			];
		}
	}
?>