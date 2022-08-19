<?php
	namespace Suphle\Tests\Integration\Routing\Nested;

	use Suphle\Hydration\Container;

	use Suphle\Contracts\Config\Router;

	use Suphle\Routing\PatternIndicator;

	use Suphle\Testing\{ TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer };

	use Suphle\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock, Middlewares\BlankMiddleware};

	use Suphle\Tests\Mocks\Modules\ModuleOne\Routes\Prefix\{ActualEntry, Secured\MisleadingEntry};

	class MultiCollectionsTest extends ModuleLevelTest {

		private $threeTierUrl = "/api/v2/first/middle/third";

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"apiStack" => [

							"v2" => MisleadingEntry::class,

							"v1" => ActualEntry::class
						]
					]);
				})
			];
		}

		/**
		 * Misleading collection tags BlankMiddleware, but the eventual collection group doesn't
		*/
		public function test_needs_recovery_from_misleading_trail () {

			$this->stubIndicator(); // given

			$this->get($this->threeTierUrl) // when

			// then
			->assertUnauthorized();

			$this->assertUsedMiddleware([BlankMiddleware::class]); 
		}

		private function stubIndicator () {

			$sutName = PatternIndicator::class;

			$this->massProvide([
				$sutName => $this->replaceConstructorArguments(
					$sutName,

					$this->getContainer()->getMethodParameters(
						
						Container::CLASS_CONSTRUCTOR, $sutName
					), [

					"resetIndications" => null
				])
			]);
		}

		public function test_can_detach_quantities_after_each_entry_collection () {

			$this->get($this->threeTierUrl) // when

			// then
			->assertOk();

			$this->assertDidntUseMiddleware([BlankMiddleware::class]);
		}
	}
?>