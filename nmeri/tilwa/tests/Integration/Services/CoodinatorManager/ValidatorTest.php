<?php
	namespace Tilwa\Tests\Integration\Services\CoodinatorManager;

	use Tilwa\Services\CoodinatorManager;

	use Tilwa\Request\ValidatorManager;

	use Tilwa\Response\RoutedRendererManager;

	use Tilwa\Contracts\{Presentation\BaseRenderer, Requests\ValidationEvaluator, Modules\DescriptorInterface};

	use Tilwa\Middleware\MiddlewareQueue;

	use Tilwa\Modules\ModuleInitializer;

	use Tilwa\Routing\RouteManager;

	use Tilwa\Exception\Explosives\{Generic\NoCompatibleValidator, ValidationFailure};

	use Tilwa\Exception\Diffusers\ValidationFailureDiffuser;

	use Tilwa\Testing\Condiments\DirectHttpTest;

	use Tilwa\Tests\Integration\Routing\TestsRouter;

	use Tilwa\Tests\Mocks\Modules\ModuleOne\{Controllers\ValidatorController, Validators\ValidatorOne};

	class ValidatorTest extends TestsRouter {

		use DirectHttpTest;

		private $controller;

		protected function setUp ():void {

			parent::setUp();

			$this->controller = $this->container->getClass(ValidatorController::class);
		}

		public function test_get_needs_no_validation () {

			// given
			$this->setHttpParams("/dummy");

			$manager = $this->container->getClass(CoodinatorManager::class);

			$error = $manager->setDependencies($this->controller, "handleGet")

			->updateValidatorMethod(); // when

			$this->assertNull($error); // then
		}

		public function test_other_methods_requires_validation () {

			$this->expectException(NoCompatibleValidator::class); // then

			// given
			$this->setHttpParams("/dummy", "post");

			$manager = $this->container->getClass(CoodinatorManager::class);

			$manager->setDependencies($this->controller, "postNoValidator")

			->updateValidatorMethod(); // when
		}

		public function test_sets_validation_rules () {

			$this->setHttpParams("/dummy", "post"); // given 1

			$validatorManager = $this->positiveDouble(ValidatorManager::class, [], [

				"setActionRules" => [1, [

					(new ValidatorOne)->postWithValidator()]]
				]
			); // then

			$this->container->whenTypeAny()->needsAny([

				ValidatorManager::class => $validatorManager
			]); // given 2

			$manager = $this->container->getClass(CoodinatorManager::class);

			$manager->setDependencies($this->controller, "postWithValidator")

			->updateValidatorMethod(); // when
		}


		public function test_failed_validation_throws_error () {

			$this->expectException(ValidationFailure::class); // then

			$validatorManager = $this->positiveDouble(ValidatorManager::class, [

				"validationErrors" => ["foo" => "bar"]
			]);

			$this->container->whenTypeAny()->needsAny([

				ValidatorManager::class => $validatorManager,

				BaseRenderer::class => $this->negativeDouble(BaseRenderer::class)
			]) // given

			->getClass(RoutedRendererManager::class)->mayBeInvalid(); // when
		}

		public function test_successful_validation_initiates_middleware () {

			$sutName = ModuleInitializer::class;

			$middlewareQueueName = MiddlewareQueue::class;

			$rendererManager = RoutedRendererManager::class;

			$container = $this->container;

			// given
			$sut = $this->replaceConstructorArguments(

				$sutName,
				[
					"descriptor" => $this->positiveDouble(DescriptorInterface::class, [

						"getContainer" => $container
					])
				],
				[
					"isLaravelRoute" => false
				]
			);

			$container->whenTypeAny()->needsAny([

				$rendererManager => $this->negativeDouble($rendererManager),

				$middlewareQueueName => $this->negativeDouble($middlewareQueueName, [], [

					"runStack" => [1, []]
				]), // then

				$sutName => $sut
			]);

			$sut->initialize()->fullRequestProtocols(); // when
		}

		public function test_failed_validation_reverts_renderer () {

			$this->setHttpParams("/dummy"); // given

			$router = $this->negativeDouble(RouteManager::class, [

				"getPreviousRenderer" => $this->negativeDouble(BaseRenderer::class, [], [

					"setRawResponse" => [

						1, [$this->callback(function($subject) {

							return array_key_exists("errors", $subject); // if getPreviousRenderer is not called, our mock won't run-- 2 verifications for the price of 1
						})] // then
					]
				]
			)]);

			$diffuser = $this->container->whenTypeAny()->needsAny([

				RouteManager::class => $router
			])
			->getClass(ValidationFailureDiffuser::class);

			$diffuser->setContextualData(

				new ValidationFailure ($this->positiveDouble(ValidationEvaluator::class))
			);

			$diffuser->prepareRendererData(); // when
		}
	}
?>