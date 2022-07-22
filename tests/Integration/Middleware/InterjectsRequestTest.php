<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Contracts\Config\Router;

	use Suphle\Middleware\Handlers\FinalHandlerWrapper;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Middlewares\{AltersPayloadStorage, HierarchialMiddleware2};

	use Suphle\Tests\Mocks\Interactions\ModuleOne;

	class InterjectsRequestTest extends ModuleLevelTest {

		private $sutName = HierarchialMiddleware2::class;
		
		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							AltersPayloadStorage::class,

							$this->sutName,

							FinalHandlerWrapper::class
						]
					])
					->replaceWithConcrete($this->sutName, $this->mockMiddleware2()); // then
				})
			];
		}

		private function mockMiddleware2 ():HierarchialMiddleware2 {

			return $this->positiveDouble($this->sutName, [

				"process" => $this->returnCallback(function($request, $requestHandler) {

					return $requestHandler->handle($request);
				})], [

				"process" => [1, [$this->callback(function($subject) {

					return $subject->hasKey("foo");

				}), $this->anything()]]
			]);
		}

		public function test_default_middleware_executes_top_to_bottom () {

			// given => @see [getModules]

			$this->get("/segment"); // when
		}
	}
?>