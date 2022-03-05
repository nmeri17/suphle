<?php
	namespace Tilwa\Tests\Integration\Routing;

	use Tilwa\Contracts\Config\Router as IRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Routes\ApiRoutes\{V1\LowerMirror, V2\ApiUpdate2Entry, V3\ApiUpdate3Entry};

	class VersioningTest extends TestsRouter {

		protected function entityBindings ():void {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => $this->positiveStub(
					RouterMock::class, [

						"apiStack" => [

							"v1" => LowerMirror::class,

							"v2" => ApiUpdate2Entry::class,

							"v3" => ApiUpdate3Entry::class
						]
					]
				)
			]);
		}

		public function test_can_get_content_at_specific_version () {

			$matchingRenderer = $this->fakeRequest("/api/v2/cascade"); // when

			$this->assertTrue($matchingRenderer->matchesHandler("secondCascade")); // then
		}

		public function test_no_version_returns_most_recent () {

			$matchingRenderer = $this->fakeRequest("/api/cascade"); // when

			$this->assertTrue($matchingRenderer->matchesHandler("thirdCascade")); // then
		}

		public function test_top_level_content_not_exist_when_request_lower_version () {

			$matchingRenderer = $this->fakeRequest("/api/v1/segment-in-second"); // when

			$this->assertNull($matchingRenderer); // then
		}
	}
?>