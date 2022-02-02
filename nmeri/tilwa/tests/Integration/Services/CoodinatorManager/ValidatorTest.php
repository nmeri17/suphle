<?php
	namespace Tilwa\Tests\Integration\Services\CoodinatorManager;

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Request\ValidatorManager;

	use Tilwa\Response\{ResponseManager, Format\AbstractRenderer};

	use Tilwa\Middleware\MiddlewareQueue;

	use Tilwa\Modules\ModuleInitializer;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Exception\Explosives\{Generic\NoCompatibleValidator, ValidationFailure};

	use Tilwa\Exception\Diffusers\ValidationFailureDiffuser;

	use Tilwa\Testing\TestTypes\IsolatedComponentTest;

	use Tilwa\Testing\Condiments\{DirectHttpTest, MockFacilitator};

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Controllers\ValidatorController, Validators\ValidatorOne};

	class ValidatorTest extends IsolatedComponentTest {

		use DirectHttpTest, MockFacilitator;

		private $controller = ValidatorController::class;

		public function test_get_needs_no_validation () {

			// given
			$this->setHttpParams("/dummy");

			$manager = $this->container->getClass(CoodinatorManager::class);

			$error = $manager->setDependencies($this->controller, "handleGet")

			->updateValidatorMethod(); // when

			$this->assertNull($error); // then
		}

		public function test_other_methods_requires_validation () {

			$this->setExpectedException(NoCompatibleValidator::class); // then

			// given
			$this->setHttpParams("/dummy", "post");

			$manager = $this->container->getClass(CoodinatorManager::class);

			$manager->setDependencies($this->controller, "postNoValidator")

			->updateValidatorMethod(); // when
		}

		public function test_sets_validation_rules () {

			$this->setHttpParams("/dummy", "post"); // given 1

			$validatorManager = $this->positiveStub(ValidatorManager::class)

			->expects($this->once())->method("setActionRules")

			->with((new ValidatorOne)->postWithValidator()); // then

			$this->container->whenTypeAny()->needsAny([

				ValidatorManager::class => $validatorManager
			]); // given 2

			$manager = $this->container->getClass(CoodinatorManager::class);

			$manager->setDependencies($this->controller, "postWithValidator")

			->updateValidatorMethod(); // when
		}


		public function test_failed_validation_throws_error () {

			$this->setExpectedException(ValidationFailure::class); // then 1

			$validatorManager = $this->positiveStub(ValidatorManager::class, [

				"validationErrors" => ["foo" => "bar"]
			])
			->expects($this->atLeastOnce())->method("validationErrors")

			->with($this->anything()); // then 2 // actually, [nothing]

			$this->container->whenTypeAny()->needsAny([

				ValidatorManager::class => $validatorManager
			]) // given

			->getClass(ResponseManager::class)->mayBeInvalid(); // when
		}

		public function test_successful_validation_initiates_middleware () {

			$sutName = ModuleInitializer::class;

			// given
			$validatorManager = $this->positiveStub(ValidatorManager::class, [

				"isValidated" => true
			]);

			$sut = $this->positiveStub($sutName, ["triggerRequest"]); // discard other method calls

			$middlewareQueue = $this->negativeStub(MiddlewareQueue::class)

			->expects($this->once())->method("runStack")

			->with($this->anything()); // then

			$this->container->whenTypeAny()->needsAny([

				ValidatorManager::class => $validatorManager,

				MiddlewareQueue::class => $middlewareQueue,

				$sutName => $sut
			])

			->getClass($sutName)->triggerRequest(); // when
		}

		public function test_failed_validation_reverts_renderer () {

			$this->setHttpParams("/dummy"); // given

			$router = $this->negativeStub(RouteManager::class, [

				"getPreviousRenderer" => $this->negativeStub(AbstractRenderer::class) // if getPreviousRenderer is not called, our mock won't run. So, 2 tests for the price of 1

					->expects($this->once())->method("setRawResponse")
					->with($this->callback(function($subject){

						return array_key_exists("errors", $subject);
					})); // then
			]);

			$this->container->whenTypeAny()->needsAny([

				RouteManager::class => $router
			])
			->getClass(ValidationFailureDiffuser::class)

			->prepareRendererData(); // when
		}
	}
?>