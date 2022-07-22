<?php
	namespace Suphle\Tests\Integration\Authorization;

	use Suphle\Contracts\Config\AuthContract;

	use Suphle\Exception\Explosives\UnauthorizedServiceAccess;

	use Suphle\Testing\{Proxies\SecureUserAssertions, Condiments\BaseDatabasePopulator};

	use Suphle\Tests\Integration\Routing\TestsRouter;

	use Suphle\Tests\Mocks\Modules\ModuleOne\Authorization\Models\EmploymentAuthorizer;

	use Suphle\Tests\Mocks\Models\Eloquent\Employment;

	class ModelAuthorizationTest extends TestsRouter {

		use BaseDatabasePopulator, SecureUserAssertions;

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

		protected function concreteBinds ():array {

			$authContract = AuthContract::class;

			return array_merge(parent::concreteBinds(), [

				$authContract => $this->positiveDouble($authContract, [

					"getModelObservers" => [

						Employment::class => EmploymentAuthorizer::class
					]
				])
			]);
		}

		public function test_unauthorized_user_cant_perform_operation () {

			$this->expectException(UnauthorizedServiceAccess::class); // then

			[$employment1, $employment2] = $this->replicator->getRandomEntities(2);

			$this->actingAs($employment2->employer->user); // given

			$employment1->update(["status" => "taken"]); // when
		}
	}
?>