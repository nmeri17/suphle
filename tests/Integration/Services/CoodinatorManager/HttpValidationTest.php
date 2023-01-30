<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer};

	use Suphle\Hydration\Container;

	use Suphle\Routing\RouteManager;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection};

	class HttpValidationTest extends ModuleLevelTest {

		protected const FAIL_NUM_TIMES = 3;

		// protected bool $debugCaughtExceptions = true;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [
			
						"browserEntryRoute" => ValidatorCollection::class
					]);
				})
			];
		}

		public function test_failed_validation_always_adds_errors_to_json_renderer () {

			for ($i = 0; $i < self::FAIL_NUM_TIMES; $i++) {

				$this->get("/get-without"); // given

				$response = $this->post("/post-with-json"); // when

				// then
				$response->assertUnprocessable()

				->assertJsonValidationErrorFor("foo")

				->assertJsonMissing(["message" => "mercy"]);
			}
		}

		public function test_failed_validation_always_reverts_errors_to_previous_on_browser () {

			for ($i = 0; $i < self::FAIL_NUM_TIMES; $i++) {

				$this->get("/get-without"); // given

				$response = $this->post("/post-with-html"); // when

				// then
				$response->assertUnprocessable()

				->assertSee("Edit form");
			}
		}
	}
?>