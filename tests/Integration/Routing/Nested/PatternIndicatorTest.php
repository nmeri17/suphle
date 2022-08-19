<?php
	namespace Suphle\Tests\Integration\Routing\Nested;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Prefix\Secured\UpperCollection, Meta\ModuleOneDescriptor, Config\RouterMock};

	use Suphle\Contracts\Config\Router;

	class PatternIndicatorTest extends ModuleLevelTest {

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => UpperCollection::class
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