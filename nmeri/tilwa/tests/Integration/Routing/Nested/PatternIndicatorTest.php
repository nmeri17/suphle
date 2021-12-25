<?php
	namespace Tilwa\Tests\Integration\Routing\Nested;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Proxies\{FrontDoorTest, WriteOnlyContainer};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\SecureUpperCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	use Tilwa\Contracts\Config\Router;

	class PatternIndicatorTest extends ModuleLevelTest {

		use FrontDoorTest {

			FrontDoorTest::setUp as frontSetup;
		};

		public function setUp () {

			$this->frontSetup();
		}

		protected function getModules():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => SecureUpperCollection::class
					]);
				})
			];
		}

		public function test_nested_route_can_unlink_auth () {

			$this->get("/prefix/unlink") // when

			->assertOk(); // then
		}

		public function test_nested_route_accesses_parent_auth () {

			$this->get("/prefix/retain-auth") // when

			->assertUnauthorized(); // then
		}
	}
?>