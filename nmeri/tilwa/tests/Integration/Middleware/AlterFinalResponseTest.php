<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\FinalHandlerWrapper;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Condiments\MockFacilitator};

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, FrontDoorTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\AlterFinalResponse};

	class AlterFinalResponseTest extends ModuleLevelTest {

		use FrontDoorTest, MockFacilitator;
		
		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							AlterFinalResponse::class,

							FinalHandlerWrapper::class
						]
					]);
				})
			];
		}

		public function test_middleware_can_alter_response () {

			$finalName = FinalHandlerWrapper::class;

			// given
			$finalMiddleware = $this->negativeStub($finalName, [
			
				"process" => ["foo" => "bar"]
			]);

			$this->getModuleFor(ModuleOne::class)->getContainer()

			->whenTypeAny()->needsAny([

				$finalName => $finalMiddleware
			]);

			$this->get("/segment") // when

			->assertJson(["foo" => "baz"]); // then
		}
	}
?>