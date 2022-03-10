<?php
	namespace Tilwa\Tests\Integration\Routing\Mirror;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	use Tilwa\Contracts\Config\Router as IRouter;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	class MirrorDeactivatedTest extends TestsRouter {

		protected function entityBindings ():void {

			parent::entityBindings();

			$this->container->whenTypeAny()->needsAny([

				IRouter::class => $this->positiveDouble(
					RouterMock::class,

					[
						"mirrorsCollections" => false,

						"browserEntryRoute" => $this->getEntryCollection()
					]
				)
			]);
		}

		public function test_disable_mirror_blocks_those_routes () {

			// given @see mock

			$matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

			$this->assertNull($matchingRenderer); // then
		}
	}
?>