<?php
	namespace Suphle\Tests\Integration\Services\CoodinatorManager;

	use Suphle\Services\CoodinatorManager;

	use Suphle\Request\ValidatorManager;

	use Suphle\Response\RoutedRendererManager;

	use Suphle\Contracts\{Presentation\BaseRenderer, Requests\ValidationEvaluator, Modules\DescriptorInterface};

	use Suphle\Middleware\MiddlewareQueue;

	use Suphle\Modules\ModuleInitializer;

	use Suphle\Routing\{RouteManager, ExternalRouteMatcher};

	use Suphle\Exception\Explosives\{Generic\NoCompatibleValidator, ValidationFailure};

	use Suphle\Exception\Diffusers\ValidationFailureDiffuser;

	use Suphle\Testing\Condiments\DirectHttpTest;

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Coordinators\ValidatorController, Validators\ValidatorOne};

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

			$sut = $this->getInitializer(); // given

			$this->injectInitializerDependencies($sut); // then

			$sut->setHandlingRenderer(); // when
		}

		private function getInitializer ():ModuleInitializer {

			return $this->replaceConstructorArguments(

				ModuleInitializer::class, [
				
					"descriptor" => $this->positiveDouble(DescriptorInterface::class, [

						"getContainer" => $this->container
					]),

					"externalRouters" => $this->positiveDouble(ExternalRouteMatcher::class, [

						"shouldDelegateRouting" => false
					])
				]
			);
		}

		private function injectInitializerDependencies (ModuleInitializer $initializer):void {

			$middlewareQueueName = MiddlewareQueue::class;

			$this->container->whenTypeAny()->needsAny([

				$middlewareQueueName => $this->negativeDouble($middlewareQueueName, [], [

					"runStack" => [1, []]
				]),

				ModuleInitializer::class => $initializer
			]);
		}
	}
?>