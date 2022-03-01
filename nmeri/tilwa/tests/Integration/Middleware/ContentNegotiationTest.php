<?php
	namespace Tilwa\Tests\Integration\Middleware;

	use Tilwa\Contracts\Config\Router;

	use Tilwa\Testing\TestTypes\ModuleLevelTest;

	use Tilwa\Testing\Proxies\{WriteOnlyContainer, FrontDoorTest};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Routes\Middlewares\MultiTagSamePattern, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ContentNegotiationTest extends ModuleLevelTest {

		use FrontDoorTest;
		
		protected function getModules ():array {

			return [
				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => MultiTagSamePattern::class
					]);
				})
			];
		}

		public function test_changes_response_type () {

			// given => @see module injection

			$this->get("/negotiate", ["Accept" => "application/json"]) // when

			->assertJson(["message" => "plain Segment"]); // then
		}
	}
?>