<?php
	namespace Suphle\Tests\Integration\Routing\Mirror;

	use Suphle\Contracts\Config\Router;

	use Suphle\Testing\Proxies\WriteOnlyContainer;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Integration\Routing\TestsRouter;

	class MirrorDeactivatedTest extends TestsRouter {

		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => $this->getEntryCollection(),

						"mirrorsCollections" => false
					]);
				})
			];
		}

		public function test_disable_mirror_blocks_those_routes () {

			// given @see mock

			$matchingRenderer = $this->fakeRequest("/api/v1/segment"); // when

			$this->assertNull($matchingRenderer); // then
		}
	}
?>