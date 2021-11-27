<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	use Tilwa\Contracts\Config\Router as IRouter;

	use Tilwa\Tests\Integration\Routing\BaseRouterTest;

	class MirrorDeactivatedTest extends BaseRouterTest {

		protected function entityBindings ():self {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => $this->positiveStub(
					RouterMock::class,

					[
						"mirrorsCollections" => false,

						"browserEntryRoute" => $this->getEntryCollection()
					]
				)
			]);

			return $this;
		}

		public function test_disable_mirror_blocks_those_routes () {

			// given @see mock

			$matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

			$this->assertNull($matchingRenderer); // then
		}
	}
?>