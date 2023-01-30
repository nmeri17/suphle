<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Request\ValidatorManager;

	use Suphle\Response\Format\Json;

	use Suphle\Middleware\MiddlewareQueue;

	use Suphle\Contracts\{Presentation\BaseRenderer, Response\RendererManager, Config\Router};

	use Suphle\Exception\Explosives\{Generic\NoCompatibleValidator, ValidationFailure};

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Validators\ValidatorOne, Routes\ValidatorCollection, Coordinators\ValidatorCoordinator};

	class ValidatorRawErrorsTest extends ModuleLevelTest {

		protected bool $debugCaughtExceptions = true;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => ValidatorCollection::class
					]);
				})
			];
		}

		public function test_get_thows_no_errors_on_rule_absence () {

			$this->get("/get-without") // when

			->assertOk(); // then
		}

		/**
		 * @dataProvider notGetUrls
		*/
		public function test_other_methods_requires_validation (string $url) {

			$this->expectException(ValidationFailure::class); // then

			// given @see coordinator validator rules
			
			$this->post($url); // when
		}

		public function notGetUrls ():array {

			return [

				["/post-with-json"], ["/post-with-html"]
			];
		}

		public function test_other_method_without_validation_fails () {

			$this->expectException(NoCompatibleValidator::class); // then

			// given @see coordinator validator rules
			
			$this->post("/post-without"); // when
		}

		public function test_sets_validation_rules () {

			$this->getRendererManager([], [ // then

				"setActionRules" => [1, [

					(new ValidatorOne)->postWithValidator()
				]]
			])
			->updateValidatorMethod(

				$this->positiveDouble(ValidatorCoordinator::class),

				"postWithValidator"
			); // when
		}


		public function test_failed_validation_throws_error () {

			$this->expectException(ValidationFailure::class); // then

			$this->getRendererManager([

				"validationErrors" => ["foo" => "bar"] // given
			])
			->mayBeInvalid(); // when
		}

		protected function getRendererManager (array $validatorStubs, array $validatorMocks = []):RendererManager {

			$this->massProvide([

				ValidatorManager::class => $this->positiveDouble(

					ValidatorManager::class, $validatorStubs,

					$validatorMocks
				),
				BaseRenderer::class => $this->positiveDouble(BaseRenderer::class)
			]);

			return $this->getContainer()->getClass(RendererManager::class);
		}

		public function test_failure_prevents_middleware_running () {

			$this->middlewareWillRun(1);

			// given @see validation rules
			$this->get("/get-without");

			$this->expectException(ValidationFailure::class);

			$this->middlewareWillRun(0); // after it's been wiped

			$this->post("/post-with-json"); // when
		}

		protected function middlewareWillRun (int $numTimes):void {

			$this->massProvide([

				MiddlewareQueue::class => $this->positiveDouble(MiddlewareQueue::class, [

					"runStack" => new Json("postWithValidator")
				], [

					"runStack" => [$numTimes, []] // then
				])
			]);
		}
	}
?>