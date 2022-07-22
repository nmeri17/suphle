<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Contracts\Config\Router;

	use Suphle\Middleware\Handlers\FinalHandlerWrapper;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\IgnoresLowerMiddleware;

	class PrematureTerminateTest extends ModuleLevelTest {

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