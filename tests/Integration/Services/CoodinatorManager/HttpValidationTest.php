<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer};

	use Suphle\Hydration\Container;

	use Suphle\Routing\RouteManager;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection};

	class HttpValidationTest extends ModuleLevelTest {

		//protected $debugCaughtExceptions = true;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [
			
						"browserEntryRoute" => ValidatorCollection::class
					]);
				})
			];
		}

		public function test_failed_validation_always_reverts_renderer_with_errors () {

			for ($i = 0; $i <4; $i++) {

				$this->get("/get-without"); // given

				$response = $this->post("/post-with"); // when

				// then
				$response->assertUnprocessable()

				->assertJsonFragment(["message" => "mercy"])

				->assertJsonValidationErrorFor("foo");
			}
		}
	}
?>