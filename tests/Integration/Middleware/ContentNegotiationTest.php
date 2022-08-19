<?php
	namespace Suphle\Tests\Integration\Middleware;

	use Suphle\Contracts\Config\Router;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Routes\Middlewares\MultiTagSamePattern, Meta\ModuleOneDescriptor, Config\RouterMock};

	class ContentNegotiationTest extends ModuleLevelTest {

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