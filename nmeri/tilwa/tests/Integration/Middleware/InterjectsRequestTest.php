<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Middleware\FinalHandlerWrapper;

	use Tilwa\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\Middlewares\{HierarchialMiddleware1, HierarchialMiddleware2};

	use Tilwa\Tests\Mocks\Interactions\ModuleOne;

	class InterjectsRequestTest extends ModuleLevelTest {
		
		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"defaultMiddleware" => [
							HierarchialMiddleware1::class,

							HierarchialMiddleware2::class,

							FinalHandlerWrapper::class
						]
					]);
				})
			];
		}

		public function test_default_middleware_executes_top_to_bottom () {

			// given => @see [getModules]

			$sutName = HierarchialMiddleware2::class;

			// then
			$hierarchial2 = $this->positiveDouble($sutName, [], [

				"process" => [1, [$this->returnCallback(function($subject) {

					return $subject->hasKey("foo");

				}), $this->anything()]]
			]);

			$this->getModuleFor(ModuleOne::class)->getContainer()

			->whenTypeAny()->needsAny([

				$sutName => $hierarchial2
			]);

			$this->get("/segment"); // when
		}
	}
?>