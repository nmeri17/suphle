<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\FinalHandlerWrapper;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, FrontDoorTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\IgnoresLowerMiddleware;

	class PrematureTerminateTest extends ModuleLevelTest {

		use FrontDoorTest;
		
		protected function getModules():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							IgnoresLowerMiddleware::class,

							FinalHandlerWrapper::class
						]
					]);
				})
			];
		}

		public function test_middleware_can_disrupt_those_below () {

			$this->get("/segment") // when

			->assertJson(["foo" => "bar"]); // then
		}
	}
?>