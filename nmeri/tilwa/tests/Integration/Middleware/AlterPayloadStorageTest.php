<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\Handlers\FinalHandlerWrapper;

	use Tilwa\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\AltersPayloadStorage, Routes\Middlewares\PayloadCollection};

	class AlterPayloadStorageTest extends ModuleLevelTest {

		private $modifierMiddleware = AltersPayloadStorage::class;
		
		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => PayloadCollection::class,

						"defaultMiddleware" => [
							$this->modifierMiddleware,

							FinalHandlerWrapper::class
						]
					]);
				})
			];
		}

		// this works because of object references. Changes to payloadStorage within the middleware affect the one stored in container
		public function test_container_must_not_provide_altered_payloadStorage () {

			$response = $this->get("/all-payload"); // when

			$middlewareInstance = $this->getContainer()->getClass($this->modifierMiddleware);

			$response->assertJson($middlewareInstance->payloadUpdates()); // then
		}
	}
?>