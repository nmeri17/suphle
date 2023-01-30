<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Contracts\Config\Router;

	use Suphle\Exception\Explosives\ValidationFailure;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection, Coordinators\ValidatorCoordinator};

	class InjectFailingMockDuringBuildTest extends ModuleLevelTest {

		// protected bool $debugCaughtExceptions = true;

		protected int $indicator = 0;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => ValidatorCollection::class
					])
					->replaceWithMock(ValidatorCoordinator::class, ValidatorCoordinator::class, [

						"postWithValidator" => $this->returnCallback(function () {

							$this->indicator = 10;

							return [];
						})
					], [

						"postWithValidator" => [1, []] // validation failure causes this not to run thus the test should fail, on grounds of an unmet expectation...
					]);
				})
			];
		}

		/**
		 * Another variant of this behavior is visible at ValidatorCoordinatorNotRunTest::test_failure_prevents_action_handling
		*/
		public function test_mock_doesnt_verify_if_execution_throws_error () {

			$this->assertSame($this->indicator, 0); // given

			$this->post("/post-with-json", [

				CsrfGenerator::TOKEN_FIELD => $this->getContainer()

				->getClass(CsrfGenerator::class)->newToken()
			])
			->assertUnprocessable(); // then // this, however, passes. We end up seeing the 3 assertions here instead of 4

			$this->assertSame($this->indicator, 0); // then 2 // ...but it doesn't
		}
	}
?>