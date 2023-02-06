<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Contracts\Config\{AuthContract, Router};

	use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

	use Suphle\Testing\Proxies\{SecureUserAssertions, WriteOnlyContainer};

	use Suphle\Testing\{Condiments\BaseDatabasePopulator, TestTypes\ModuleLevelTest};

	use Suphle\Tests\Mocks\Modules\ModuleOne\{Meta\ModuleOneDescriptor, Config\RouterMock, Authorization\Models\EmploymentAuthorizer, Routes\Auth\AuthorizeRoutes};

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class ModelAuthorizationTest extends ModuleLevelTest {

		use BaseDatabasePopulator, SecureUserAssertions;

		protected bool $debugCaughtExceptions = true;

		protected function getModules ():array {

			return [

				$this->replicateModule(ModuleOneDescriptor::class, function (WriteOnlyContainer $container) {

					$container->replaceWithMock(Router::class, RouterMock::class, [

						"browserEntryRoute" => AuthorizeRoutes::class
					])
					->replaceWithMock(AuthContract::class, AuthContract::class, [

							"getModelObservers" => [

								Employment::class => EmploymentAuthorizer::class
							]
						]
					);
				})
			];
		}

		protected function getActiveEntity ():string {

			return Employment::class;
		}

		public function test_authorized_user_can_perform_operation () {

			// given
			$employment = $this->replicator->getRandomEntity();

			$this->actingAs($employment->employer->user);

			$this->assertTrue( // then

				$employment->update(["status" => "taken"]) // when
			);
		}

		public function test_unauthorized_user_cant_perform_operation () {

			$this->expectException(UnauthorizedServiceAccess::class); // then

			[$employment1, $employment2] = $this->replicator->getRandomEntities(2);

			$this->actingAs($employment2->employer->user); // given

			$employment1->update(["status" => "taken"]); // when
		}
	}
?>