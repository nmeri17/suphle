<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\Handlers\FinalHandlerWrapper;

	use Tilwa\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\AlterFinalResponse};

	class AlterFinalResponseTest extends ModuleLevelTest {
		
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
			$finalMiddleware = $this->negativeDouble($finalName, [
			
				"process" => ["foo" => "bar"]
			]);

			$this->getModules()[0]->getContainer()->whenTypeAny() // safe to happen here since modules are booted for each test, as compared to doing this on app entry

			->needsAny([

				$finalName => $finalMiddleware
			]);

			$this->get("/segment") // when

			->assertJson(["foo" => "baz"]); // then
		}
	}
?>