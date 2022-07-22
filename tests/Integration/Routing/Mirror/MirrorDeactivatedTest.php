<?php
	namespace Suphle\Tests\Integration\Routing\Mirror;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Config\RouterMock;

	use Suphle\Contracts\Config\Router as IRouter;

	use Suphle\Tests\Integration\Routing\TestsRouter;

	class MirrorDeactivatedTest extends TestsRouter {

		protected function concreteBinds ():array {

			return array_merge(parent::concreteBinds(), [

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