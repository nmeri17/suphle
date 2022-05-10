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

					$finalName = FinalHandlerWrapper::class;

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							AlterFinalResponse::class,

							$finalName
						]
					])
					->replaceWithMock($finalName, $finalName, [
					
						"process" => ["foo" => "bar"]
					], [], false);
				})
			];
		}

		public function test_middleware_can_alter_response () {

			// given @see module injection

			$this->get("/segment") // when

			->assertJson(["foo" => "baz"]); // then
		}
	}
?>