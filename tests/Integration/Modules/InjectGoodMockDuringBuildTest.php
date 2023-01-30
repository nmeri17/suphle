<?php
	namespace Suphle\Tests\Integration\Modules;

	use Suphle\Security\CSRF\CsrfGenerator;

	use Suphle\Contracts\Config\Router;

	use Suphle\Exception\Explosives\ValidationFailure;

	use Suphle\Testing\{TestTypes\ModuleLevelTest, Proxies\WriteOnlyContainer};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Routes\ValidatorCollection, Coordinators\ValidatorCoordinator};

	class InjectGoodMockDuringBuildTest extends ModuleLevelTest {

		// protected bool $debugCaughtExceptions = true;

		protected int $indicator = 0;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => ValidatorCollection::class
					])
					->replaceWithMock(ValidatorCoordinator::class, 

						ValidatorCoordinator::class, [

							"handleGet" => $this->returnCallback(function () {

								$this->indicator = 10;

								return [];
							})
						], [

						"handleGet" => [0, []] // then // if that method actually runs, this ought to cause the test to fail
					]);
				})
			];
		}

		public function test_build_level_mock_verifies () {

			$this->assertSame($this->indicator, 0); // given

			$this->get("/get-without")->assertStatus(500); // when // the internal PHPUnit failure causes response to return 500. If the mock is adjusted to verify one call, request no longer fails

			$this->assertSame($this->indicator, 10); // then 2 // if it doesn't run, this fails
		}
	}
?>