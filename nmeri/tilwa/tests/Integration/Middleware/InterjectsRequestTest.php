<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\FinalHandlerWrapper;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\{HierarchialMiddleware1, HierarchialMiddleware2};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class InterjectsRequestTest extends ModuleLevelTest {

		private $sutName = HierarchialMiddleware2::class;
		
		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							HierarchialMiddleware1::class,

							$this->sutName,

							FinalHandlerWrapper::class
						]
					])
					->replaceWithConcrete($this->sutName, $this->mockMiddleware2()); // then
				})
			];
		}

		private function mockMiddleware2 ():HierarchialMiddleware2 {

			return $this->positiveDouble($this->sutName, [], [

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