<?php
	namespace Tilwa\Tests\Integration\Routing\Nested;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Proxies\{FrontDoorTest, WriteOnlyContainer};

	use Tilwa\Testing\Condiments\MockFacilitator;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{ Meta\ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\};

	use Tilwa\Contracts\Config\Router;

	class MultiCollectionsTest extends ModuleLevelTest {

		use FrontDoorTest, MockFacilitator {

			FrontDoorTest::setUp as frontSetup;
		};

		private $nestedUrl = "/first/second/third";

		public function setUp () {

			$this->frontSetup();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"apiStack" => [

							MisleadingEntry::class,

							ActualEntry::class
						]
					]);
				})
			];
		}

		public function test_needs_recovery_from_misleading_trail () {

			$this->stubIndicator(); // given

			$this->get($this->nestedUrl) // when

			// then
			->assertUnauthorized();

			$this->assertUsedMiddleware([/*names*/]); 
		}

		private function stubIndicator () {

			$sutName = PatternIndicator::class;

			foreach ($this->getModules() as $descriptor)

				$descriptor->getContainer()->whenTypeAny()

				->needsAny([$sutName => $this->positiveStub($sutName, [

						"resetIndications" => null
					])
				]);
		}

		public function test_can_detach_quantities_after_each_entry_collection () {

			$this->get($this->nestedUrl) // when

			// then
			->assertOk();

			$this->assertDidntUseMiddleware([/*names*/]);
		}
	}
?>