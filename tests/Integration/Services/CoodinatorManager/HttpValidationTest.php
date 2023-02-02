<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Contracts\{Config\Router, Presentation\BaseRenderer, Response\RendererManager, Requests\ValidationEvaluator};

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Response\Format\Json;

	use Suphle\Request\RequestDetails;

	use Suphle\Hydration\Container;

	use Suphle\Exception\{Diffusers\ValidationFailureDiffuser, Explosives\ValidationFailure};

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection, Coordinators\ValidatorCoordinator};

	class HttpValidationTest extends ModuleLevelTest {

		protected const FAIL_RETRY_TIMES = 3;

		// protected bool $debugCaughtExceptions = true;

		protected array $csrfField;

		protected function setUp ():void {

			parent::setUp();

			$this->csrfField = [

				CsrfGenerator::TOKEN_FIELD => $this->getContainer()

				->getClass(CsrfGenerator::class)->newToken()
			];
		}

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [
			
						"browserEntryRoute" => ValidatorCollection::class
					]);
				})
			];
		}

		public function test_failed_validation_calls_diffuser_builder () {

			$this->massProvide([

				RendererManager::class => $this->getRendererManager()
			]);

			$response = $this->post("/post-with-json", $this->csrfField); // when
		}

		protected function getRendererManager ():RoutedRendererManager {

			$renderer = new Json("postWithValidator");

			$renderer->setCoordinatorClass($this->positiveDouble(ValidatorCoordinator::class));

			return $this->replaceConstructorArguments(

				RoutedRendererManager::class, [

				RequestDetails::class => $this->positiveDouble(RequestDetails::class, [

					"isGetRequest" => false
				]),
				BaseRenderer::class => $renderer
			], [

				"mayBeInvalid" => $this->throwException(

					$this->getValidationException($renderer)
				) // then
			]);
		}

		protected function getValidationException (BaseRenderer $rendererToReturn):ValidationFailure {

			return $this->positiveDouble(ValidationFailure::class, [

				"getEvaluator" => $this->positiveDouble(ValidationEvaluator::class, [

					"validationRenderer" => $rendererToReturn
				], [

					"validationRenderer" => [1, [

						$this->callback(function ($subject) {

							return empty(array_diff(array_keys($subject), [

								ValidationFailureDiffuser::PAYLOAD_KEY,

								ValidationFailureDiffuser::ERRORS_PRESENCE
							]));
						})
					]]
				])
			]);
		}

		public function test_failed_validation_always_adds_errors_to_json_renderer () {

			for ($i = 0; $i < self::FAIL_RETRY_TIMES; $i++) {

				$this->get("/get-without"); // given

				$response = $this->post("/post-with-json", $this->csrfField); // when

				// then
				$response->assertUnprocessable()

				->assertJsonValidationErrorFor(

					"foo", ValidationFailureDiffuser::ERRORS_PRESENCE
				)
				->assertJsonMissing(["message" => "mercy"]);
			}
		}

		public function test_failed_validation_always_reverts_errors_to_previous_on_browser () {

			for ($i = 0; $i < self::FAIL_RETRY_TIMES; $i++) {

				$this->get("/get-without"); // given

				$response = $this->post("/post-with-html", $this->csrfField); // when

				// then
				$response->assertUnprocessable()

				->assertSee("Edit form");
			}
		}
	}
?>